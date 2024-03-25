<?php

namespace Webkul\Admin\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\Core\Models\Channel;
use Webkul\Core\Models\Locale;
use Webkul\Inventory\Repositories\InventorySourceRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Ui\DataGrid\DataGrid;

class RecipeDataGrid extends DataGrid
{
    /**
     * Default sort order of datagrid.
     *
     * @var string
     */
    protected $sortOrder = 'desc';

    /**
     * Set index columns, ex: id.
     *
     * @var string
     */
    protected $index = 'id';

    /**
     * If paginated then value of pagination.
     *
     * @var int
     */
    protected $itemsPerPage = 10;

    /**
     * Locale.
     *
     * @var string
     */
    protected $locale = 'all';

    /**
     * Channel.
     *
     * @var string
     */
    protected $channel = 'all';

    /**
     * Contains the keys for which extra filters to show.
     *
     * @var string[]
     */
    protected $extraFilters = [
        'channels',
        'locales',
    ];

    /**
     * Product repository instance.
     *
     * @var \Webkul\Product\Repositories\ProductRepository
     */
    protected $productRepository;

    /**
     * Inventory source repository instance.
     *
     * @var \Webkul\Inventory\Repositories\InventorySourceRepository
     */
    protected $inventorySourceRepository;

    /**
     * Create datagrid instance.
     *
     * @param  \Webkul\Product\Repositories\ProductRepository  $productRepository
     * @param  \Webkul\Inventory\Repositories\InventorySourceRepository  $inventorySourceRepository
     * @return void
     */
    public function __construct(
        ProductRepository $productRepository,
        InventorySourceRepository $inventorySourceRepository
    ) {
        parent::__construct();

        /* locale */
        $this->locale = core()->getRequestedLocaleCode();

        /* channel */
        $this->channel = core()->getRequestedChannelCode();

        /* finding channel code */
        if ($this->channel !== 'all') {
            $this->channel = Channel::query()->find($this->channel);
            $this->channel = $this->channel ? $this->channel->code : 'all';
        }

        $this->productRepository = $productRepository;

        $this->inventorySourceRepository = $inventorySourceRepository;
    }

    /**
     * Prepare query builder.
     *
     * @return void
     */
    public function prepareQueryBuilder()
    {
        if ($this->channel === 'all') {
            $whereInChannels = Channel::query()->pluck('code')->toArray();
        } else {
            $whereInChannels = [$this->channel];
        }

        if ($this->locale === 'all') {
            $whereInLocales = Locale::query()->pluck('code')->toArray();
        } else {
            $whereInLocales = [$this->locale];
        }

        /* query builder */
        $queryBuilder = DB::table('recipes');

        $this->setQueryBuilder($queryBuilder);
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function addColumns()
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('admin::app.datagrid.id'),
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'slug',
            'label'      => trans('admin::app.datagrid.slug'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
        ]);

        // $this->addColumn([
        //     'index'      => 'product_number',
        //     'label'      => trans('admin::app.datagrid.product-number'),
        //     'type'       => 'string',
        //     'searchable' => true,
        //     'sortable'   => true,
        //     'filterable' => true,
        // ]);

        // $this->addColumn([
        //     'index'      => 'product_name',
        //     'label'      => trans('admin::app.datagrid.name'),
        //     'type'       => 'string',
        //     'searchable' => true,
        //     'sortable'   => true,
        //     'filterable' => true,
        // ]);

        // $this->addColumn([
        //     'index'      => 'attribute_family',
        //     'label'      => trans('admin::app.datagrid.attribute-family'),
        //     'type'       => 'string',
        //     'searchable' => true,
        //     'sortable'   => true,
        //     'filterable' => true,
        // ]);

        // $this->addColumn([
        //     'index'      => 'product_type',
        //     'label'      => trans('admin::app.datagrid.type'),
        //     'type'       => 'string',
        //     'sortable'   => true,
        //     'searchable' => true,
        //     'filterable' => true,
        // ]);

        // $this->addColumn([
        //     'index'      => 'status',
        //     'label'      => trans('admin::app.datagrid.status'),
        //     'type'       => 'boolean',
        //     'sortable'   => true,
        //     'searchable' => false,
        //     'filterable' => true,
        //     'closure'    => function ($value) {
        //         if ($value->status == 1) {
        //             return trans('admin::app.datagrid.active');
        //         } else {
        //             return trans('admin::app.datagrid.inactive');
        //         }
        //     },
        // ]);

        // $this->addColumn([
        //     'index'      => 'price',
        //     'label'      => trans('admin::app.datagrid.price'),
        //     'type'       => 'price',
        //     'sortable'   => true,
        //     'searchable' => false,
        //     'filterable' => true,
        // ]);

        // $this->addColumn([
        //     'index'      => 'quantity',
        //     'label'      => trans('admin::app.datagrid.qty'),
        //     'type'       => 'number',
        //     'sortable'   => true,
        //     'searchable' => false,
        //     'filterable' => false,
        //     'closure'    => function ($row) {
        //         if (is_null($row->quantity)) {
        //             return 0;
        //         } else {
        //             return $this->renderQuantityView($row);
        //         }
        //     },
        // ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        $this->addAction([
            'title'     => trans('admin::app.datagrid.edit'),
            'method'    => 'GET',
            'route'     => 'admin.catalog.recipes.edit',
            'icon'      => 'icon pencil-lg-icon',
            'condition' => function () {
                return true;
            },
        ]);

        $this->addAction([
            'title'        => trans('admin::app.datagrid.delete'),
            'method'       => "POST",
            'route'        => 'admin.catalog.recipes.delete',
            // 'confirm_text' => trans('ui::app.datagrid.massaction.delete', ['resource' => 'recipe']),
            'icon'         => 'icon trash-icon',
        ]);
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        // $this->addAction([
        //     'title'  => trans('admin::app.datagrid.copy'),
        //     'method' => 'GET',
        //     'route'  => 'admin.catalog.recipes.copy',
        //     'icon'   => 'icon copy-icon',
        // ]);

        // $this->addMassAction([
        //     'type'   => 'delete',
        //     'label'  => trans('admin::app.datagrid.delete'),
        //     'action' => route('admin.catalog.recipes.massdelete'),
        //     'method' => 'POST',
        // ]);

        // $this->addMassAction([
        //     'type'    => 'update',
        //     'label'   => trans('admin::app.datagrid.update-status'),
        //     'action'  => route('admin.catalog.recipes.massupdate'),
        //     'method'  => 'POST',
        //     'options' => [
        //         'Active'   => 1,
        //         'Inactive' => 0,
        //     ],
        // ]);
    }

    /**
     * Render quantity view.
     *
     * @parma  object  $row
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    private function renderQuantityView($row)
    {
        $product = $this->productRepository->find($row->product_id);

        $inventorySources = $this->inventorySourceRepository->findWhere(['status' => 1]);

        $totalQuantity = $row->quantity;

        return view('admin::catalog.recipes.datagrid.quantity', compact('product', 'inventorySources', 'totalQuantity'))->render();
    }
}
