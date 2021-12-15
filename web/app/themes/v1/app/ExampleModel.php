<?php
namespace App\Model;

use Platform\Model;

class ExampleModel extends Model {

    protected static $table = 'Orders';
    protected static $cache_by_key = 'id';

    public $id;
    public $date_created;
    public $name;

    /**
     * @return string
     */
    public function query()
    {
        $q = 'SELECT SQL_CALC_FOUND_ROWS
                    o.*
                FROM Orders AS o
                WHERE 1=1
                    {where_id}
                ORDER BY
                    o.date_created DESC
                {limit}';

        $this->fields([
            'id' => [
                'column' => 'o.id',
                'type' => self::INTEGER
            ],
            'name' => [
                'column' => 'o.name',
                'type' => self::STRING
            ],
            'date_created' => [
                'type' => self::DATETIME
            ]
        ]);

        return $q;
    }

}
