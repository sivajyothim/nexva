<?php

/**
 * Get the Vendor name of an app
 */
class Nexva_View_Helper_TicketStatus extends Zend_View_Helper_Abstract {

  /**
   * Returns ticket status
   *
   * @param $priority priority
   * @return $timeDiffof time diff.
   */
  public function TicketStatus($priority, $timeDiff) 
  {    
        switch ($priority) 
        {
            case 'Urgent';

                if($timeDiff < 720)
                {
                    echo '<span class="new subject_style">NEW</span><br/>';
                    $dueIn = round((720 - $timeDiff)/60);
                    echo 'Due in about '.$dueIn.' hours';
                }
                else
                {
                    $hours = round(($timeDiff - 720)/60);
                    echo '<span class="overdue subject_style">OVERDUE</span><br/>';
                    echo 'Overdue by about '.$hours.' hours';
                }
                break;

            case 'High'; 

                if($timeDiff < 1440)
                {
                    echo '<span class="new subject_style">NEW</span><br/>';
                    $dueIn = round((1440 - $timeDiff)/60);
                    echo 'Due in about '.$dueIn.' hours';
                }
                else
                {
                    $hours = round(($timeDiff - 1440)/60);
                    echo '<span class="overdue subject_style">OVERDUE</span><br/>';
                    echo 'Overdue by about '.$hours.' hours';

                }                                    
                break;

             case 'Medium';                                      

                if($timeDiff < 2160)
                {
                    echo '<span class="new subject_style">NEW</span><br/>';
                    $dueIn = round((2160 - $timeDiff)/60);
                    echo 'Due in about '.$dueIn.' hours';
                }
                else
                {
                    $hours = round(($timeDiff - 2160)/60);
                    echo '<span class="overdue subject_style">OVERDUE</span><br/>';
                    echo 'Overdue by about '.$hours.' hours';

                }                                   
                break;

             case 'Low'; 

                if($timeDiff < 2880)
                {
                    echo '<span class="new subject_style">NEW</span><br/>';
                    $dueIn = round((2880 - $timeDiff)/60);
                    echo 'Due in about '.$dueIn.' hours';
                }
                else
                {
                    $hours = round(($timeDiff - 2880)/60);
                    echo '<span class="overdue subject_style">OVERDUE</span><br/>';
                    echo 'Overdue by about '.$hours.' hours';
                }                                  
                break;
        }           
  }

}

?>