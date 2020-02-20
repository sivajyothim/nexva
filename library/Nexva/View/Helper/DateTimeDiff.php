<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rooban
 * Date: 04/11/13
 * Time: 06:20 PM
 */

class Nexva_View_Helper_DateTimeDiff extends Zend_View_Helper_Abstract
{

    /**
     * @return date time diff array array
     * */
    public function DateTimeDiff($createdDate)
    {
        //get date time diff
        $dateTimeAdded = new DateTime($createdDate);
        $dateTimeNow = new DateTime(date('Y-m-d H:i:s'));

        $dateTimeDiff = $dateTimeNow->diff($dateTimeAdded);
        //print_r($dateTimeDiff); echo $dateTimeDiff->days; die();
        $TicketAddedHours = $dateTimeDiff->format("%D:%H:%I:%S");

        $ticketTime = explode(':', $TicketAddedHours);
        $days = $ticketTime[0];
        $hours = $ticketTime[1];
        $minutes = $ticketTime[2];
        $seconds = $ticketTime[3];

        $totalHours = ($days*24) + $hours;
        $totalMinutes = ($days*24*60) + ($hours*60) + $minutes;
        $totalSeconds = ($days*24*60*60) + ($hours*60*60) + ($minutes*60) + $seconds;
        
        $dateTimeDiffNew = '';
        
        if($totalSeconds < 60):
            $dateTimeDiffNew = '('.$totalSeconds.' Seconds Ago)';
        
        elseif($totalMinutes < 60):
            $dateTimeDiffNew = '('.$totalMinutes.' Minutes Ago)';

        elseif($totalHours < 48):
            $dateTimeDiffNew = '('.$totalHours.' Hours Ago)';

        else:
            $dateTimeDiffNew = '('.$dateTimeDiff->days.' Days Ago)';

        endif; 
                                        
        //$dateTimeDiffArr = array('total_days' => $days, 'total_hours' => $totalHours, 'total_minutes' => $totalMinutes);
        return $dateTimeDiffNew;
    }
}