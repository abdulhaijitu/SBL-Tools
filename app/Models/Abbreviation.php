<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Abbreviation extends Model
{
    protected $fillable = ['code', 'name', 'category', 'category_slug', 'meaning_bn', 'description_bn', 'icon', 'tag'];
}
