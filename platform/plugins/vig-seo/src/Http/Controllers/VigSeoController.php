<?php

namespace VigStudio\VigSeo\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Exception;
use Illuminate\Http\Request;
use VigStudio\VigSeo\Forms\VigSeoForm;
use VigStudio\VigSeo\Http\Requests\VigSeoRequest;
use VigStudio\VigSeo\Repositories\Interfaces\VigSeoInterface;
use VigStudio\VigSeo\Tables\VigSeoTable;

class VigSeoController extends BaseController
{
    protected VigSeoInterface $vigSeoRepository;

    public function __construct(VigSeoInterface $vigSeoRepository)
    {
        $this->vigSeoRepository = $vigSeoRepository;
    }

    /**
     * @param  VigSeoTable  $table
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(VigSeoTable $table)
    {
        page_title()->setTitle(trans('plugins/vig-seo::vig-seo.name'));

        return $table->renderTable();
    }

    /**
     * @param  FormBuilder  $formBuilder
     * @return string
     */
    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle(trans('plugins/vig-seo::vig-seo.create'));

        return $formBuilder->create(VigSeoForm::class)->renderForm();
    }

    /**
     * @param  VigSeoRequest  $request
     * @param  BaseHttpResponse  $response
     * @return BaseHttpResponse
     */
    public function store(VigSeoRequest $request, BaseHttpResponse $response)
    {
        $vigSeo = $this->vigSeoRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(VIG_SEO_MODULE_SCREEN_NAME, $request, $vigSeo));

        return $response
            ->setPreviousUrl(route('vig-seo.index'))
            ->setNextUrl(route('vig-seo.edit', $vigSeo->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    /**
     * @param  int  $id
     * @param  Request  $request
     * @param  FormBuilder  $formBuilder
     * @return string
     */
    public function edit($id, FormBuilder $formBuilder, Request $request)
    {
        $vigSeo = $this->vigSeoRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $vigSeo));

        page_title()->setTitle(trans('plugins/vig-seo::vig-seo.edit').' "'.$vigSeo->name.'"');

        return $formBuilder->create(VigSeoForm::class, ['model' => $vigSeo])->renderForm();
    }

    /**
     * @param  int  $id
     * @param  VigSeoRequest  $request
     * @param  BaseHttpResponse  $response
     * @return BaseHttpResponse
     */
    public function update($id, VigSeoRequest $request, BaseHttpResponse $response)
    {
        $vigSeo = $this->vigSeoRepository->findOrFail($id);

        $vigSeo->fill($request->input());

        $vigSeo = $this->vigSeoRepository->createOrUpdate($vigSeo);

        event(new UpdatedContentEvent(VIG_SEO_MODULE_SCREEN_NAME, $request, $vigSeo));

        return $response
            ->setPreviousUrl(route('vig-seo.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    /**
     * @param  int  $id
     * @param  Request  $request
     * @param  BaseHttpResponse  $response
     * @return BaseHttpResponse
     */
    public function destroy(Request $request, $id, BaseHttpResponse $response)
    {
        try {
            $vigSeo = $this->vigSeoRepository->findOrFail($id);

            $this->vigSeoRepository->delete($vigSeo);

            event(new DeletedContentEvent(VIG_SEO_MODULE_SCREEN_NAME, $request, $vigSeo));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @param  BaseHttpResponse  $response
     * @return BaseHttpResponse
     *
     * @throws Exception
     */
    public function deletes(Request $request, BaseHttpResponse $response)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        foreach ($ids as $id) {
            $vigSeo = $this->vigSeoRepository->findOrFail($id);
            $this->vigSeoRepository->delete($vigSeo);
            event(new DeletedContentEvent(VIG_SEO_MODULE_SCREEN_NAME, $request, $vigSeo));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
