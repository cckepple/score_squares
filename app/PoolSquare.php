<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Log;

class PoolSquare extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pool_squares';

    // status types
    const STATUS_OPEN = 1;
    const STATUS_PENDING = 2;
    const STATUS_OWNED = 3;

    public static $STATUSES = array(
       array('id'=>1,'name'=>'Available'),
       array('id'=>2,'name'=>'Pending Payment'),
       array('id'=>3,'name'=>'Claimed'),
    );

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function getStatusAttribute($value)
    {
        return self::findTypeById(self::$STATUSES, $value);
    }

    public static function claimSquares($squares, $paidCount)
    {
        for ($i=0; $i < $paidCount; $i++) { 
            $squares[$i]->status = PoolSquare::STATUS_OWNED;
            $squares[$i]->save();
        }
    } 

    public static function unPaySquares($squares, $unPaidCount)
    {
        for ($i=0; $i < $unPaidCount; $i++) { 
            $squares[$i]->status = PoolSquare::STATUS_PENDING;
            $squares[$i]->save();
        }
    }

    public static function unClaimSquares($squares, $unClaimCount)
    {
        for ($i=0; $i < $unClaimCount; $i++) { 
            $squares[$i]->user_id = null;
            $squares[$i]->status = PoolSquare::STATUS_OPEN;
            $squares[$i]->save();
        }
    }

    public static function findTypeById($types, $id, $defaultIndex = 0)
    {
        $derivedType = isset($types[$defaultIndex]) ? $types[$defaultIndex] : null;
        foreach ($types as $type)
        {
            if (isset($type['id']) && $type['id'] == $id)
            {
                $derivedType = $type;
                break;
            }
        }
        return $derivedType;
    }
}
