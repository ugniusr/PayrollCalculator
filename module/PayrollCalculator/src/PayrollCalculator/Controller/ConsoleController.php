<?php
namespace PayrollCalculator\Controller;

use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Prompt;


class ConsoleController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel(); // display standard index page
    }
    
    public function outputpaydaysAction()
    {
        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console!');
        }

        // Get filename from console 
        $fileName  = $request->getParam('fileName');
        $fileName = chop($fileName); // remove whitespace

        // CONFIG:
        $extension = "csv";

        // Check if the filename includes the .CSV extension
        if (!$this->HasCorrectExtension($fileName, $extension))
        {
            return "Invalid extention. Has to be ." . $extension . "\n";
        }
        
        // Generate pay day array to be written to the file
        $CSVdata = $this->GeneratePayDates();

        // Write the pay days to file
        if ($this->WriteDataToFile($CSVdata, $fileName))
        {
            return "Done! The paydays for the remainder of the year are saved in $fileName.\n"; 
        }
        else
        {
            return "\n";
        }
    }

    private function GeneratePayDates()
    {
        // CONFIG:
        $month_array = array('January','February','March','April','May','June',
                            'July','August','September','October','November','December');
        $bonuspayday = 15;
        date_default_timezone_set('UTC');
        $today = date('Y-m-d');
        $CSVdata = array();
        $CSVdata[] = "Month,BonusDate,SalaryDate";

        // Generate PayDay array to be written to the file
        for ($i = intval(date("m")); $i <= 12; $i++)
        {
            $_SalaryDay = $this->getSalaryDay($i);
            $_BonusDay = $this->getBonusDay($i, $bonuspayday);
            
            if (strtotime($_SalaryDay) < strtotime($today))
                $_SalaryDay = '';
            if (strtotime($_BonusDay) < strtotime($today))
                $_BonusDay = '';
            
            if (!empty($_SalaryDay) || !empty($_BonusDay))
            {
                $CSVdata[] = $month_array[$i-1] . "," . $_SalaryDay . "," . $_BonusDay . "\n";
            }
        }
        return $CSVdata;
    }

    private function WriteDataToFile(Array $data, $fileName)
    {
        // Check if the file already exists
        if (file_exists($fileName))
        {
            // If it exists, offer to overwrite it
            if (!Prompt\Confirm::prompt('This file already exists. Would you like to overwrite it? [y/n]', 'y', 'n'))
                return false;
            else
                unlink($fileName);
        }

        // Create the file
        $handle = fopen($fileName, 'w') or die('Cannot create or open file:  '.$fileName);

        // Write data in variable $data to file $fileName
        for ($j = 1; $j <= count($data); $j++)
        {
            fwrite($handle, $data[$j-1]);
        }

        // Close the file
        fclose($handle);

        return true;
    }

    private function getSalaryDay($month)
    {
        // CONFIG:
        $year = date('Y');

        // DEFAULT VALUE: (last day of the month)
        $SalaryDay = date("Y-m-t", strtotime($year . '-' . $month . '-' . '15'));
        $weekday = intval(date("N", strtotime($SalaryDay)));
        
        // IF WEEKEND:
        if ($weekday == 6)
            $SalaryDay = date('Y-m-d', strtotime('-1 day', strtotime($SalaryDay)));
        if ($weekday == 7)
            $SalaryDay = date('Y-m-d', strtotime('-2 days', strtotime($SalaryDay)));

        return $SalaryDay;
    }

    private function getBonusDay($month, $defaultday)
    {
        // CONFIG:
        $year = date('Y');
        
        // DEFAULT VALUE:
        $BonusDay =  $year .'-' . $month . '-' . $defaultday; 
        $weekday = intval(date("N", strtotime($BonusDay)));

        // IF WEEKEND:
        if ($weekday == 6)
            $BonusDay = date('Y-m-d', strtotime('+4 days', strtotime($BonusDay)));
        if ($weekday == 7)
            $BonusDay = date('Y-m-d', strtotime('+3 days', strtotime($BonusDay)));        

        return $BonusDay;  
    }

    private function HasCorrectExtension($fileName, $extension)
    {
        // just to make sure the extension has a dot in the beginning.
        $extension = (substr($extension, 1) != ".") ? "." . $extension : $extension;  

        // extract file extension from the end of the string
        $ext_compare = substr($fileName, -1 * strlen($extension)); 
        return (strtolower($ext_compare) === strtolower($extension)) ?  true : false;
    }

}