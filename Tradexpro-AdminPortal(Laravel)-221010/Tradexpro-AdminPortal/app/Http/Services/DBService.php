<?php

namespace App\Http\Services;


use Illuminate\Support\Facades\DB;

class DBService
{
    /**
     * Begin transaction for multiple DB connection
     */
    public static function beginTransaction()
    {
        DB::beginTransaction();
    }

    /**
     * Rollback transaction for multiple DB connection
     */
    public static function rollBack()
    {
        DB::rollBack();
    }

    /**
     * Commit transaction for multiple DB connection
     */
    public static function commit()
    {
        DB::commit();
    }
}
