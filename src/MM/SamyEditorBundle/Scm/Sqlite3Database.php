<?php
namespace MM\SamyEditorBundle\Scm;

use PDO;

class Sqlite3Database
{
    /**
     * @var string
     */
    protected $filepath;

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
        $this->setFilepath(tempnam('/tmp/', 'MM'));
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
        $pdo = new \PDO('sqlite:' . $this->getFilepath());
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    /**
     * @return string $binary
     */
    public function getBinary()
    {
        return file_get_contents($this->getFilepath());
    }

}