<?php
namespace Portal\Core\Models;

class Model
{
    /**
     * ODBC Verbindung zu AS400
     */
    protected $odbc;

    protected $hinweis = [];

    public function __construct(){
        //$this->$odbc = odbc_connect('LOGA', 'WEBPROF', 'LMP#0794E');
    }
    /**
     * Verbinded sich mit der AS400
     *
     * @return obdc
     */
    protected function connectAS400(){
        return odbc_connect('LOGA', 'WEBPROF', 'LMP#0794E');
    }
    /**
     * Satzsperre, da nur ein User einen neuen Satz hinzufügen darf 
     *
     * @return bool
     */
    protected function satzSperren(){
        $fs = fopen(ROOT . '/files/Satzsperre.dat');
        $sperre = fgets($fs);
        fclose($fs);
        if($sperre == 'Satz_gesperrt'){
            $this->hinweis[0] = 9; //Hinzufügen nicht möglich, Satz gesperrt;
            return $this->getHinweis();
        }
        $fs=fopen(ROOT . '/files/Satzsperre.dat', 'w');
        fwrite($fs, 'Satz_gesperrt');
        fclose($fs);
    }
    /**
     * Gibt alle Hinweise zurück
     *
     * @return array
     */
    public function getHinweis(){
        return $this->hinweis;
    }

    public function setHinweis($lfdNr, $index=null){
        if(null === $index){
            $this->hinweis[] = $lfdNr;
        }else{
            $this->hinweis[$index] = $lfdNr;
        }
        return $this;
    }
    /**
     * Gibt den Datensatz wieder frei
     *
     * @return void
     */
    protected function satzFreigeben(){
        $fs=fopen(ROOT . '/files/Satzsperre.dat');
        fwrite($fs, 'Satz_frei');
        fclose($fs);
        return $this;
    }
    /**
     * Sucht nach dem User und dem gefordertem Recht in der Datenbank
     * @param string $user
     * @param string|null $recht
     *
     * @return array
     */
    protected function getCurrentUser($user, $recht=null){
        $conn = $this->connectAS400();
        $sql = 'Select LESEN, SCHREIBEN, AENDERN from logistik.AUTL0P where AUT_User = \'%1$s\'';
        if(isset($recht) && null !== $recht){
            $sql .= ' and RECHT = \'%2$s\'';
        }
        $query = sprintf($sql, $user, $recht);
        $result = odbc_exec($conn, $query);
        $response = [0];
        while(odbc_fetch_row($result)){
            $response = [
                1,
                odbc_result($result, 'LESEN'),
                odbc_result($result, 'SCHREIBEN'),
                odbc_result($result, 'AENDERN'),
            ];
        }
        return $response;
    }
}

