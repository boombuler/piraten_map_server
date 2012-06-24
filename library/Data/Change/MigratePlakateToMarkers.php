<?php
class Data_Change_MigratePlakateToMarkers extends Data_Change
{
    private $doStep1 = true;
    private $doStep2 = true;
    private $doStep3and4 = true;

    function __construct ()
    {
        $this->setId('CreateMarkerTable');
    }
    /**
     *
     * @see Data_Change::__invoke()
     */
    public function __invoke ()
    {
        $this->checkIfNeedToMigrate();
        if ($this->doStep1) {
            $this->step1CreateMarkersTable();
        }
        if ($this->doStep2) {
            $this->step2CreatePostersTable();
        }
        if ($this->doStep3and4) {
            $this->step3TransferDataToNewTables();
            $this->step4RemoveOldTables();
        }
        $this->setStatus(true);
        return true;
    }

    private function checkNecessarySteps() {
        $res = System::query('SHOW TABLES');
        $tables = $res->fetchAll(PDO::FETCH_COLUMN, 0);
        $this->doStep1 = !in_array(System::getConfig('tbl_prefix') . 'markers', $tables);
        $this->doStep2 = !in_array(System::getConfig('tbl_prefix') . 'posters', $tables);
        $this->doStep3and4 = in_array(System::getConfig('tbl_prefix') . 'felder', $tables) || in_array(System::getConfig('tbl_prefix') . 'plakat', $tables);
    }

    private function step1CreateMarkersTable()
    {
        return System::query('CREATE TABLE IF NOT EXISTS ' . System::getConfig('tbl_prefix') . 'markers (
                                marker_id int not null auto_increment,
                                lat double not null,
                                lon double not null,
                                city varchar(255) default null,
                                street varchar(255) default null,
                                primary key(marker_id),
                                unique key (lat, lon)
                              ) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
    }

    private function step2CreatePostersTable()
    {
        return System::query('CREATE TABLE `' . System::getConfig('tbl_prefix') . 'posters` (
                               `poster_id` int(11) NOT NULL AUTO_INCREMENT,
                               `marker_id` int(11) NOT NULL,
                               `username` varchar(50) NOT NULL,
                               `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                               `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT "default",
                               `comment` text COLLATE utf8_unicode_ci DEFAULT NULL,
                               `image` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
                               PRIMARY KEY (`poster_id`),
                               KEY (marker_id),
                               KEY (username)
                             ) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
    }

    private function step3TransferDataToNewTables()
    {
        $markers = System::query('INSERT IGNORE INTO ' . System::getConfig('tbl_prefix') . 'markers (lat, lon, city, street) '
                               . 'SELECT lat, lon, city, street FROM ' . System::getConfig('tbl_prefix') . 'felder f '
                               . 'JOIN ' . System::getConfig('tbl_prefix') . 'plakat p ON p.id=f.plakat_id WHERE p.del!=1 GROUP BY p.plakat_id');
        $rows = System::getDb()->affected_rows;
        $check = System::query('SELECT COUNT(id) amount FROM ' . System::getConfig('tbl_prefix') . 'plakat WHERE del!=1');
        $check = $check->fetch_assoc();
        if ($rows != $check['amount']) {
          throw new Exception('Copying Data failed.');
        }

        return System::query('INSERT IGNORE INTO ' . System::getConfig('tbl_prefix') . 'posters (marker_id, user_id, timestamp, type, comment, image) '
                           . 'SELECT m.marker_id, f.username, f.timestamp, f.type, f.comment, f.image '
                           . 'FROM ' . System::getConfig('tbl_prefix') . 'felder f '
                           . 'JOIN ' . System::getConfig('tbl_prefix') . 'markers m ON m.lat=f.lat AND m.lon=f.lon '
                           . 'WHERE f.user != ""');
    }

    private function step4RemoveOldTables()
    {
        System::query('DROP TABLE IF EXISTS ' . System::getConfig('tbl_prefix') . 'felder');
        System::query('DROP TABLE IF EXISTS ' . System::getConfig('tbl_prefix') . 'plakat');
    }
}
