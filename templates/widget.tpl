[widget_header]

<aside id="intel-calendar-widget" class="widget">
  <div id="intel-user-calendar"></div>
</aside>  

<script>

  var americanHolidayDates = {};
  var personalHolidayDates = {};
  var publicHolidayDates = {};
  
  [personalHolidays]
  
  [publicHolidays]
  
  [americanHolidays]
  
  $( document).ready( function() 
  {
    $( "#intel-user-calendar" ).datepicker(
    {
      beforeShowDay: function(date) 
      {
        var highlightAmerican = americanHolidayDates[date];
        var highlightPersonal = personalHolidayDates[date];
        var highlightPublic = publicHolidayDates[date];
        var cssclass = '';
        
        if( highlightAmerican ) 
        {
          cssclass = "americanholiday";
        }
        else if( highlightPersonal ) 
        {
          cssclass = "personalholiday";
        }
        else if( highlightPublic ) 
        {
          cssclass = "publicholiday";
        }        
        
        if (highlightAmerican | highlightPersonal | highlightPublic) 
        {
          return [true, cssclass, ''];
        } 
        else 
        {
          return [true, '', ''];
        }        
      },
      autoSize: true
    });
  });
</script>