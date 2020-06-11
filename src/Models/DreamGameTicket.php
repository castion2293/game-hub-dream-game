<?php

namespace SuperStation\Gamehub\DreamGame\Models;

use SuperStation\Gamehub\Models\RawTicket;

class DreamGameTicket extends RawTicket
{
    /**
     * 取得唯一的識別碼
     */
    public function getUuidAttribute()
    {
        return $this->uniqueToUuid([
                   $this->userName,
                   $this->id
               ])->__toString();
    }

    /**
     * 取得原生注單各欄位串接起來的雜揍狀態，供比對使用
     */
    public function getMd5HashAttribute()
    {
        if (array_key_exists('md5_hash', $this->attributes)) {
            return $this->attributes['md5_hash'];
        }

        return $this->md5HashAttributes($this->attributes);
    }
}