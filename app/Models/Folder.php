<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'google_drive_id',
        'parent_folder_id'
    ];

    /**
     * Define the relationship with the `User` model for many-to-many access.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'folder_user', 'folder_id', 'user_id')->withTimestamps();
    }

    /**
     * Define the relationship with itself to manage folder hierarchy.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_folder_id');
    }

    /**
     * Define the relationship to access the parent folder.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_folder_id');
    }
}
