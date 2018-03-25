<?php
/**
 * \Elabftw\Elabftw\AbstractMake
 *
 * @package   Elabftw\Elabftw
 * @author    Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @license   https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @see       https://www.elabftw.net Official website
 */
namespace Elabftw\Elabftw;

use Symfony\Component\HttpFoundation\Request;

/**
 * Mother class of MakeCsv, MakePdf and MakeZip
 *
 */
abstract class AbstractMake
{
    /** @var AbstractEntity $Entity instance of Experiments or Database */
    protected $Entity;

    /** @var Db $Db SQL Database */
    protected $Db;

    /**
     * Constructor
     *
     * @param AbstractEntity $entity
     */
    public function __construct(AbstractEntity $entity)
    {
        $this->Entity = $entity;
        $this->Db = Db::getConnection();
    }


    /**
     * The filename for what we are making
     *
     * @return string
     */
    abstract public function getCleanName(): string;

    /**
     * Generate a long and unique string
     *
     * @return string a sha512 hash of uniqid()
     */
    protected function getUniqueString(): string
    {
        return hash("sha512", uniqid(rand(), true));
    }

    /**
     * Get the uploads folder absolute path
     *
     * @return string absolute path
     */
    protected function getUploadsPath(): string
    {
        return ELAB_ROOT . 'uploads/';
    }

    /**
     * Get the temporary files folder absolute path
     *
     * @return string absolute path
     */
    protected function getTmpPath(): string
    {
        return ELAB_ROOT . 'uploads/tmp/';
    }

    /**
     * Return the url of the item or experiment
     *
     * @return string url to the item/experiment
     */
    protected function getUrl(): string
    {
        $Request = Request::createFromGlobals();
        $url = Tools::getUrl($Request) . '/' . $this->Entity->page . '.php';

        return $url . "?mode=view&id=" . $this->Entity->id;
    }
}
