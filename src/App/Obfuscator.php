<?php

namespace MikeR\App;

class Obfuscator
{
    private $path_input_script;
    private $path_out_script;
    private $path_config;

    /**
     * Obfuscator constructor.
     * @param $path_input_script
     * @param $path_out_script
     */
    public function __construct($path_input_script,$path_out_script,$path_config)
    {
        $this->path_input_script = $path_input_script;
        $this->path_out_script = $path_out_script;
        $this->path_config = $path_config;
    }


    public function run()
    {
        $filecon = ($this->path_input_script)?
                    (file_get_contents($this->path_input_script, "r")):
                    null;
        $config = include($this->path_config);
        $confTblName = array_keys($config)[0];//in other cases should create array of tables
        $confFlds = array_values($config)[0];
        $lines = file($this->path_input_script);
        $keyword= 'INSERT INTO';
        $keywordVal='VALUES';
        $isValues=false;
        $handle = fopen($this->path_out_script, "a+") or die("Unable to open file!");
        $outcontent='';
        foreach ($lines as $line_num => $line) { //go on sql lines
            if (strpos($line, $keyword) !== false ) //if INSERT INTO `table` section
            {
                $key=substr($line,strlen($keyword) );
                $key = substr($key,0,strpos($key,"(")); //get table from sql
                if(strpos($key,$confTblName) !=false){ //if sql table =  config table
                    $sqlFlds=str_replace(array( '(', ')' ), '',substr($line,strpos($line,"("),strpos($line,")")));
                    $sqlFlds =  explode( ',', str_replace('`','',$sqlFlds )) ;
                    $sqlFlds= array_map('trim', $sqlFlds);// array of sql fileds
                    $conf_array = [];
                    foreach($sqlFlds as $key=> $val){ // go on sql fields
                        if(in_array($val,$confFlds)){ // if sql fields = config fields
                            $conf_array[] = $key;
                        }
                    }
                }
            }
            if($isValues && strpos($line,'(') &&  strpos($line,')')) //if ('4232456@mail.ru' ....), or ('4232456@mail.ru' ....);
            {
                $value= str_replace(array( '(', ')' ), '',substr($line,strpos($line,"("),strpos($line,")")));
                $valuearr = explode( ',', str_replace('`','',$value )) ; //values in array
                foreach($conf_array as $fkey=>$fval)
                {
                    $newvalue = $this->xor_this($valuearr[$fval]); //encrypt info, for decrypt start once more with xor
                    $valuearr[$fval] = $newvalue;
                }
                $newvalue= '('.implode(',',$valuearr).')';
                $line = $newvalue."\n";
                if(strpos($line,'(') && strpos($line,');'))
                    $isValues=false;
            }
            if(strpos($line, $keywordVal) !== false && !empty($conf_array))
                $isValues =true;
            $outcontent .= $line."\n";
        }
        fwrite($handle, $outcontent);
        fclose($handle);
    }


    public function xor_this($string)
    {
        // Let's define our key here
        $key = ('cryptokey');
        // Our plaintext/ciphertext
        $text = $string;
        // Our output text
        $outText = '';
        // Iterate through each character
        for($i=0; $i<strlen($text); )
        {
            for ($j = 0; ($j < strlen($key) && $i < strlen($text)); $j++, $i++)
                $outText .= $text{$i} ^ $key{$j};
        }
        return $outText;
    }
}
