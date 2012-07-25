<?php
class Data_Change_CreateDatabase extends Data_Change
{
    private $createRegions = true;
    private $createUsers = true;

    function __construct ()
    {
        $this->setId('CreateDatabase');
    }
	
	/**
	 * return an array of changes, that have to be executed successfully before this can be run.
	 */
	public function getDependencies()
	{
	    return array('CreateDatabaseChangesTable');
	}
	
    /**
     *
     * @see Data_Change::__invoke()
     */
    public function __invoke ()
    {
        $this->checkNecessarySteps();
        if ($this->createRegions) {
            $this->doCreateRegions();
        }
        if ($this->createUsers) {
            $this->step2CreatePostersTable();
        }
        $this->setStatus(true);
        return true;
    }

    private function checkNecessarySteps() 
	{
        $this->createRegions = !System::tableExists('regions');
        $this->createUsers = !System::tableExists('users');
    }

    private function doCreateRegions()
    {
        return System::query('CREATE TABLE IF NOT EXISTS ' . System::getConfig('tbl_prefix') . 'regions (
									`id` int(11) NOT NULL AUTO_INCREMENT,
									`category` varchar(50) NOT NULL,
									`lat` double NOT NULL,
									`lon` double NOT NULL,
									`zoom` int(11) NOT NULL,
									PRIMARY KEY (`id`)
							  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
    }

    private function doCreateUsers()
    {
        return System::query('CREATE TABLE `' . System::getConfig('tbl_prefix') . 'users` (
								`id` int(11) NOT NULL AUTO_INCREMENT,
								`username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
								`password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
								`email` VARCHAR( 256 ) COLLATE utf8_unicode_ci NULL ,
								`admin` tinyint(1) NOT NULL DEFAULT \'0\',
								PRIMARY KEY (`id`)
							  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
    }
}
