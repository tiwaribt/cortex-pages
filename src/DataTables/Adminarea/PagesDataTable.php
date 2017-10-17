<?php

declare(strict_types=1);

namespace Cortex\Pages\DataTables\Adminarea;

use Rinvex\Pages\Contracts\PageContract;
use Cortex\Foundation\DataTables\AbstractDataTable;
use Cortex\Pages\Transformers\Adminarea\PageTransformer;

class PagesDataTable extends AbstractDataTable
{
    /**
     * {@inheritdoc}
     */
    protected $model = PageContract::class;

    /**
     * {@inheritdoc}
     */
    protected $transformer = PageTransformer::class;

    /**
     * Get the query object to be processed by dataTables.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function query()
    {
        $locale = app()->getLocale();
        $query = app($this->model)->query()->orderBy('sort_order', 'ASC')->orderBy("title->\${$locale}", 'ASC');

        return $this->applyScopes($query);
    }

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        $transformer = app($this->transformer);

        return datatables()->eloquent($this->query())
                           ->setTransformer($transformer)
                           ->orderColumn('title', 'title->"$.'.app()->getLocale().'" $1')
                           ->make(true);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $link = config('cortex.foundation.route.locale_prefix')
            ? '"<a href=\""+routes.route(\'adminarea.pages.edit\', {page: full.slug, locale: \''.$this->request->segment(1).'\'})+"\">"+data+"</a>"'
            : '"<a href=\""+routes.route(\'adminarea.pages.edit\', {page: full.slug})+"\">"+data+"</a>"';

        return [
            'title' => ['title' => trans('cortex/pages::common.title'), 'render' => $link.'+(full.is_active ? " <i class=\"text-success fa fa-check\"></i>" : " <i class=\"text-danger fa fa-close\"></i>")', 'responsivePriority' => 0],
            'uri' => ['title' => trans('cortex/pages::common.uri')],
            'route' => ['title' => trans('cortex/pages::common.route')],
            'view' => ['title' => trans('cortex/pages::common.view')],
            'middleware' => ['title' => trans('cortex/pages::common.middleware')],
            'created_at' => ['title' => trans('cortex/pages::common.created_at'), 'render' => "moment(data).format('MMM Do, YYYY')"],
            'updated_at' => ['title' => trans('cortex/pages::common.updated_at'), 'render' => "moment(data).format('MMM Do, YYYY')"],
        ];
    }
}
