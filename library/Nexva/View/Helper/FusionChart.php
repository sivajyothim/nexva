<?php

class Nexva_View_Helper_FusionChart extends Zend_View_Helper_Abstract{

    public function fusionchart($data = ''){


   $data   =   "<chart caption='Yearly downloads of Zgroup' xAxisName='Month' yAxisName='Downloads count' showValues='0' formatNumberScale='0' showBorder='1'>
  <set label='Jan' value='462' />
  <set label='Feb' value='857' />
  <set label='Mar' value='671' />
  <set label='Apr' value='494' />
  <set label='May' value='761' />
  <set label='Jun' value='960' />
  <set label='Jul' value='629' />
  <set label='Aug' value='622' />
  <set label='Sep' value='376' />
  <set label='Oct' value='494' />
  <set label='Nov' value='761' />
  <set label='Dec' value='960' />
</chart>";

       require_once(APPLICATION_PATH."/../public/vendors/FusionCharts/Code/PHP/Includes/FusionCharts.php");

       return  renderChart("/vendors/FusionCharts/Charts/Column3D.swf", "", $data, "factory",600, 300, false, true);       
       
    }    
}




?>

