<?php
namespace Webkul\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Contracts\CustomerGroup as CustomerGroupContract;

class CustomerGroup extends Model implements CustomerGroupContract
{
    public const GENERAL = 2;
    public const SUNBULAH_GROUP = 4;
    protected $table = 'customer_groups';

    protected $fillable = ['name', 'code', 'is_user_defined'];

    /**
     * Get the customers for this group.
    */
    public function customers()
    {
        return $this->hasMany(CustomerProxy::modelClass());
    }
}
