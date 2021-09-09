<?php

namespace Models;
use Core\Model;


class Warehouse extends Model {
    
    const CONFIG_FN = 'ovdmConfig.json';
    const LOWERING_CONFIG_FN = 'loweringConfig.json';
    const MANIFEST_FN = 'manifest.json';
    
    private $_cruises, $_lowerings;

    public function getShoresideDataWarehouseBaseDir(){
        return CRUISEDATA_BASEDIR;
    }

    public function getShoresideDataWarehouseApacheDir(){
        return CRUISEDATA_APACHEDIR;
    }
    
    public function getLoweringDataBaseDir(){
        return LOWERINGDATA_BASEDIR;
    }

    public function getCruises(){
        
        if (!is_array($this->_cruises) || (is_array($this->_cruises) && sizeof($this->_cruises) == 0)) {
        
            $baseDir = $this->getShoresideDataWarehouseBaseDir();
            #var_dump($baseDir);
            //Get the list of directories
            if (is_dir($baseDir)) {
                $rootList = scandir($baseDir);
                #var_dump($rootList);

                foreach ($rootList as $rootKey => $rootValue)
                {
                    if (!in_array($rootValue,array(".","..")))
                    {
                        if (is_dir($baseDir . DIRECTORY_SEPARATOR . $rootValue))
                        {
                            //Check each Directory for ovdmConfig.json
                            $cruiseList = scandir($baseDir . DIRECTORY_SEPARATOR . $rootValue);
                            #var_dump($cruiseList);
                            foreach ($cruiseList as $cruiseKey => $cruiseValue){
                                #var_dump($cruiseValue);
                                if (in_array($cruiseValue,array(self::CONFIG_FN))){
                                    #var_dump($baseDir . DIRECTORY_SEPARATOR . $rootValue . DIRECTORY_SEPARATOR . self::CONFIG_FN);
                                    $ovdmConfigContents = file_get_contents($baseDir . DIRECTORY_SEPARATOR . $rootValue . DIRECTORY_SEPARATOR . self::CONFIG_FN);
                                    $ovdmConfigJSON = json_decode($ovdmConfigContents,true);
                                    #var_dump($ovdmConfigJSON['extraDirectoriesConfig']);
                                    //Get the the directory that holds the DashboardData
				                    for($i = 0; $i < count($ovdmConfigJSON['extraDirectoriesConfig']); $i++){
                                        if(strcmp($ovdmConfigJSON['extraDirectoriesConfig'][$i]['name'], 'Dashboard_Data') === 0){
                                            $dataDashboardList = scandir($baseDir . DIRECTORY_SEPARATOR . $rootValue . DIRECTORY_SEPARATOR . $ovdmConfigJSON['extraDirectoriesConfig'][$i]['destDir']);
					                        #var_dump($dataDashboardList);
					                        foreach ($dataDashboardList as $dataDashboardKey => $dataDashboardValue){
                                                //If a manifest file is found, add CruiseID to output
                                                if (in_array($dataDashboardValue,array(self::MANIFEST_FN))){
                                                    $this->_cruises[] = $rootValue;
                                                    break;
                                                }
                                            }
                                            break;
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            #var_dump($this->_cruises);

            if(is_array($this->_cruises) && count($this->_cruises) > 0) {
                rsort($this->_cruises);
            }
            return $this->_cruises;
        } else {
            return array("Error"=>"Could not find base directory.");
        }
    }
    
    public function getLatestCruise() {
        return $this->getCruises()[0];
    }

    public function getLowerings(){
        // var_dump($this->_lowerings); 
        if (!$this->_lowerings || (is_array($this->_lowerings) && sizeof($this->_lowerings) == 0)) {
        
            $baseDir = $this->getShipboardDataWarehouseBaseDir();
            $cruiseDir = $baseDir . DIRECTORY_SEPARATOR . $this->getCruiseID();
            $loweringDataBaseDir = $cruiseDir . DIRECTORY_SEPARATOR . $this->getLoweringDataBaseDir();
            #var_dump($baseDir);
            //Get the list of directories
            if (is_dir($loweringDataBaseDir)) {
                $rootList = scandir($loweringDataBaseDir);
                #var_dump($rootList);

                foreach ($rootList as $rootKey => $rootValue)
                {
                    if (!in_array($rootValue,array(".","..")))
                    {
                        if (is_dir($loweringDataBaseDir . DIRECTORY_SEPARATOR . $rootValue) && is_readable($loweringDataBaseDir . DIRECTORY_SEPARATOR . $rootValue))
                        {
                            //Check each Directory for ovdmConfig.json
                            $loweringList = scandir($loweringDataBaseDir . DIRECTORY_SEPARATOR . $rootValue);
                            #var_dump($cruiseList);
                            foreach ($loweringList as $loweringKey => $loweringValue){
                                #var_dump($loweringValue);
                                if (in_array($loweringValue,array(self::LOWERING_CONFIG_FN))){
                                    #var_dump($loweringDataBaseDir . DIRECTORY_SEPARATOR . $rootValue . DIRECTORY_SEPARATOR . self::LOWERING_CONFIG_FN);
                                    $loweringConfigContents = file_get_contents($loweringDataBaseDir . DIRECTORY_SEPARATOR . $rootValue . DIRECTORY_SEPARATOR . self::LOWERING_CONFIG_FN);
                                    $loweringConfigJSON = json_decode($loweringConfigContents,true);
                                    #var_dump($ovdmConfigJSON['extraDirectoriesConfig']);
                                    //Get the the directory that holds the DashboardData
                                    $this->_lowerings[] = $rootValue;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            //var_dump($this->_lowerings);

            //If there are no lowerings
            if(!$this->_lowerings) {
                return array();
            }

            if(is_array($this->_lowerings) && sizeof($this->_lowerings) > 0) {
                rsort($this->_lowerings);
            }
            return $this->_lowerings;
        } else {
            return array("Error"=>"Could not find base directory.");
        }
    }

    public function getLatestLowering() {
        return $this->getLowerings()[0];
    }
}
