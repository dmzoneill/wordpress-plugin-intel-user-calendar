<?php

define( "HOLIDAY_TYPE_PERSONAL", "HOLIDAY_TYPE_PERSONAL" );
define( "HOLIDAY_TYPE_PUBLIC", "HOLIDAY_TYPE_PUBLIC" );
define( "HOLIDAY_TYPE_AMERICAN", "HOLIDAY_TYPE_AMERICAN" );

class Intel_User_Calendar_Widget extends WP_Widget 
{
	private $mssql = null;
  
	public function __construct() 
	{
		$this->mssql = new Mssql();
		
		$id_base = 'intel-user-calendar-widget';
		$name = 'Intel User Calendar';
		$widget_options = array( 'description' => 'Show user calendar' );
		$control_options = array();
    
		parent::__construct( $id_base, $name, $widget_options, $control_options );
	}
  
	public function widget( $args, $instance ) 
	{    
		$output = "Unable to connect to database";
		
		if( $this->mssql->is_connected() == false )
		{
			echo $output;
			return;
		}    
		
		$widgetHeader = "";
		
		if ( ! empty( $instance['title'] ) ) 
		{
			$widgetHeader = $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}    
		
		/* Pay Dates: https://ease.intel.com/es/mypay/MyPay.ashx?action=date&CtryCd=IRL&wwid=11302960 */
		/* Pay details: https://ease.intel.com/es/mypay/MyPay.ashx?action=detail&CtryCd=IRL&wwid=11302960&PayDate=11/05/2015&LangCd=EN */
		/* Pay: https://ease.intel.com/es/mypay/IRLView.aspx?LinkID=26927 */
		/* PI: https://ease.intel.com/es/home/PSAppContainer.aspx?linkid=15880 */
		/* Education: https://ease.intel.com/es/home/PSAppContainer.aspx?linkid=15878 */
		/* Pay modeeling: https://ease.intel.com/ES/FocalTool/PayModelingApp.aspx?linkid=202 */
		
		$common = Common::getInstance();        
		$template = file_get_contents( realpath( dirname( __FILE__ ) ) . "/templates/widget.tpl" );
		$output = preg_replace( "/" . preg_quote( "[widget_header]" ) . "/", $widgetHeader, $template );    
		$output = preg_replace( "/" . preg_quote( "[user_picture_url]" ) . "/", esc_url( home_url( '/' ) ) . "/wp-content/themes/intel/" . $common->getuserimage( $common->current_user , $ldap ), $output ); 
		$output = preg_replace( "/" . preg_quote( "[publicHolidays]" ) . "/", $this->getDates( HOLIDAY_TYPE_PUBLIC ), $output );   
		$output = preg_replace( "/" . preg_quote( "[personalHolidays]" ) . "/", $this->getDates( HOLIDAY_TYPE_PERSONAL ), $output );
		$output = preg_replace( "/" . preg_quote( "[americanHolidays]" ) . "/", $this->getDates( HOLIDAY_TYPE_AMERICAN ), $output );    
		
		echo $output;
	}
  
	private function getDates( $type )
	{
		$retdates = array();   
    
		switch( $type )
		{
			case HOLIDAY_TYPE_PERSONAL:
		  
				$common = Common::getInstance();
				$wwid = $common->current_user->employeeid;

				$leaveperiods = $this->mssql->query( "SELECT [datStartDate],[strStartTime],[datEndDate],[strEndTime],[strLeaveTypeName],[strRequestComments]      
										  FROM [dbo].[qryLeavePeriodDetails]
										  WHERE strEEWWID = '$wwid'
										  AND datRejected IS NULL
										  AND datCancelRequested IS NULL
										  AND datCancelApproved IS NULL
										  AND datCancelRejected IS NULL
										  ORDER BY datStartDate" );
										  
				foreach ( $leaveperiods as $row ) 
				{
					$startdate = strtotime( $row['datStartDate'] );
					$enddate = strtotime( $row['datEndDate'] );    
					$desc = trim( $row['strRequestComments'] );

					$weekends_excluded = array();          
					$dates = new DatePeriod(new DateTime( date('Y-m-d' , $startdate) ), new DateInterval('P1D'), new DateTime( date('Y-m-d' , $enddate + (3600*24) ) ) );

					foreach( $dates as $date )
					{
						if( $date->format("N") == 6 || $date->format("N") == 7 )
						{
							continue;
						}
						$weekends_excluded[] = $date;
					}

					$retdates[] = "//$desc";

					if( date('Y-m-d' , $startdate) == date('Y-m-d' , $enddate) )
					{
						$year = date('Y' , $startdate );
						$month = date('m' , $startdate )-1;
						$day = date('d' , $startdate );
						$retdates[] = "personalHolidayDates[ new Date( $year,$month,$day ) ] = new Date( $year,$month,$day );";
						continue;
					}

					foreach( $weekends_excluded as $date )
					{
						$year = $date->format("Y");
						$month = $date->format("m")-1;
						$day = $date->format("d");

						$retdates[] = "personalHolidayDates[ new Date( $year,$month,$day ) ] = new Date( $year,$month,$day );";
					}
				}
			
			break;
      
			case HOLIDAY_TYPE_PUBLIC:
			  
				$leaveperiods = $this->mssql->query( "SELECT * FROM [dbo].[tblPublicHoliday]" );
													  
				foreach ( $leaveperiods as $row ) 
				{
					$startdate = strtotime( $row['datDate'] );
					$desc = trim( $row['strDescription'] );

					$year = date('Y' , $startdate );
					$month = date('m' , $startdate )-1;
					$day = date('d' , $startdate );

					$retdates[] = "publicHolidayDates[ new Date( $year,$month,$day ) ] = new Date( $year,$month,$day );";
				}
				
			break;
			  
			case HOLIDAY_TYPE_AMERICAN:
			  
				$IntelUserCalendarPlugin = Intel_User_Calendar_Plugin::getInstance();
				$leaveperiods = preg_split( "/[\s,]+/" , $IntelUserCalendarPlugin->get_setting('american_holidays') );
				
				foreach ( $leaveperiods as $date ) 
				{
					$holiday = trim( $date );
					if( $holiday == "" ) continue;

					$startdate = strtotime( $holiday );

					$year = date('Y' , $startdate );
					$month = date('m' , $startdate )-1;
					$day = date('d' , $startdate );

					$retdates[] = "americanHolidayDates[ new Date( $year,$month,$day ) ] = new Date( $year,$month,$day );";
				}
				
			break;
		}  

		return implode( "\n" , $retdates );    
	}
    
	public function update( $new_instance, $old_instance ) 
	{
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
  
	public function form( $instance ) 
	{
		$title = ! empty( $instance['title'] ) ? $instance['title'] : 'New title';
		
		$template = file_get_contents( realpath( dirname( __FILE__ ) ) . "/templates/widget_form.tpl" );
		$output = preg_replace( "/" . preg_quote( "[for_title]" ) . "/", $this->get_field_id( 'title' ), $template );
		$output = preg_replace( "/" . preg_quote( "[e_title]" ) . "/", _e( 'Title:' ), $output );
		$output = preg_replace( "/" . preg_quote( "[field_id]" ) . "/", $this->get_field_id( 'title' ), $output );
		$output = preg_replace( "/" . preg_quote( "[field_name]" ) . "/", $this->get_field_name( 'title' ), $output );
		$output = preg_replace( "/" . preg_quote( "[field_value]" ) . "/", esc_attr( $title ), $output );
		
		echo $output;
	}	
}