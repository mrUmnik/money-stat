<?php
/**
 * Created by mr.Umnik.
 */

namespace MoneyStat\Entity;


use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
	public $timestamps = false;
	protected $guarded = ['id'];
}