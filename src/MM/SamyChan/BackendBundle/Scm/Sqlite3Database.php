<?php
namespace MM\SamyChan\BackendBundle\Scm;

use PDO;
use SQLite3;

class Sqlite3Database
{
    /**
     * @var string
     */
    protected $filepath;

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var SQLite3
     */
    protected $sqlite3;

    /**
     * @return string
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    /**
     * @param string $filepath
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;
    }

    /**
     * @param string $binarayData
     */
    public function __construct($binarayData)
    {
        // @todo: fix absolute pathes
        $tmppath = __DIR__ . '/../../../../../app/tmp';
        if (!file_exists($tmppath)) {
            mkdir($tmppath);
        }
        $this->setFilepath(tempnam(realpath($tmppath), 'MM'));
        file_put_contents($this->getFilepath(), $binarayData);
    }

    public function __destruct()
    {
        unlink($this->getFilepath());
    }

    /**
     * @return PDO
     */
    public function getPdo()
    {
        if (!isset($this->pdo)) {
            $this->pdo = new \PDO('sqlite:' . $this->getFilepath());
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // After endless debugging: the db has WAL enabled by default. therefore
            // all modifications gets written to a WAL file. the original db-file
            // has no modifications. disable this behavoir
            $this->pdo->query("PRAGMA journal_mode=DELETE");
        }

        return $this->pdo;
    }

    /**
     * @return SQLite3
     */
    public function getSqlite3()
    {
        if (!isset($this->sqlite3)) {
            $this->sqlite3 = new SQLite3($this->getFilepath());
        }

        return $this->sqlite3;
    }

    /**
     * @return bool
     */
    public function disconnect()
    {
        return $this->getSqlite3()->close();
    }

    /**
     * @return string $binary
     */
    public function getBinary()
    {
        return file_get_contents($this->getFilepath());
    }

}