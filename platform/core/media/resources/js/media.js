import './jquery.doubletap'
import { MediaConfig } from './App/Config/MediaConfig'
import { Helpers } from './App/Helpers/Helpers'
import { MediaService } from './App/Services/MediaService'
import { FolderService } from './App/Services/FolderService'
import { UploadService } from './App/Services/UploadService'
import { ActionsService } from './App/Services/ActionsService'
import { DownloadService } from './App/Services/DownloadService'
import { EditorService } from './integrate'

class MediaManagement {
    constructor() {
        this.MediaService = new MediaService()
        this.UploadService = new UploadService()
        this.FolderService = new FolderService()
        this.DownloadService = new DownloadService()

        this.$body = $('body')
    }

    init() {
        Helpers.resetPagination()
        this.setupLayout()

        this.handleMediaList()
        this.changeViewType()
        this.changeFilter()
        this.search()
        this.handleActions()

        this.UploadService.init()

        this.handleModals()
        this.scrollGetMore()
    }

    setupLayout() {
        /**
         * Sidebar
         */
        const $currentFilter = $(
            `.js-rv-media-change-filter[data-type="filter"][data-value="${Helpers.getRequestParams().filter}"]`
        )

        $currentFilter
            .closest('button.dropdown-item')
            .addClass('active')
            .closest('.dropdown')
            .find('.js-rv-media-filter-current')
            .html(`(${$currentFilter.html()})`)

        const $currentViewIn = $(
            `.js-rv-media-change-filter[data-type="view_in"][data-value="${Helpers.getRequestParams().view_in}"]`
        )

        $currentViewIn
            .closest('button.dropdown-item')
            .addClass('active')
            .closest('.dropdown')
            .find('.js-rv-media-filter-current')
            .html(`(${$currentViewIn.html()})`)

        if (Helpers.isUseInModal()) {
            $('.rv-media-footer').removeClass('d-none')
        }

        /**
         * Sort
         */
        $(`.js-rv-media-change-filter[data-type="sort_by"][data-value="${Helpers.getRequestParams().sort_by}"]`)
            .closest('button.dropdown-item')
            .addClass('active')

        /**
         * Details pane
         */
        let $mediaDetailsCheckbox = $('#media_details_collapse')
        $mediaDetailsCheckbox.prop('checked', MediaConfig.hide_details_pane || false)

        setTimeout(() => {
            $('.rv-media-details').show()
        }, 300)

        $mediaDetailsCheckbox.on('change', (event) => {
            event.preventDefault()
            MediaConfig.hide_details_pane = $(event.currentTarget).is(':checked')
            Helpers.storeConfig()
        })

        $(document).on('click', '.js-download-action', (event) => {
            event.preventDefault()
            $('#modal_download_url').modal('show')
        })

        $(document).on('click', '.js-create-folder-action', (event) => {
            event.preventDefault()
            $('#modal_add_folder').modal('show')
        })
    }

    handleMediaList() {
        let _self = this

        /*Ctrl key in Windows*/
        let ctrl_key = false

        /*Command key in MAC*/
        let meta_key = false

        /*Shift key*/
        let shift_key = false

        $(document).on('keyup keydown', (e) => {
            /*User hold ctrl key*/
            ctrl_key = e.ctrlKey
            /*User hold command key*/
            meta_key = e.metaKey
            /*User hold shift key*/
            shift_key = e.shiftKey
        })

        _self.$body
            .off('click', '.js-media-list-title')
            .on('click', '.js-media-list-title', (event) => {
                event.preventDefault()
                let $current = $(event.currentTarget)

                if (shift_key) {
                    let firstItem = Helpers.arrayFirst(Helpers.getSelectedItems())
                    if (firstItem) {
                        let firstIndex = firstItem.index_key
                        let currentIndex = $current.index()
                        $('.rv-media-items li').each((index, el) => {
                            if (index > firstIndex && index <= currentIndex) {
                                $(el).find('input[type=checkbox]').prop('checked', true)
                            }
                        })
                    }
                } else if (!ctrl_key && !meta_key) {
                    $current.closest('.rv-media-items').find('input[type=checkbox]').prop('checked', false)
                }

                let $lineCheckBox = $current.find('input[type=checkbox]')
                $lineCheckBox.prop('checked', true)
                ActionsService.handleDropdown()

                _self.MediaService.getFileDetails($current.data())

                // Add to recent items when a file is clicked
                if (!$current.data('is_folder')) {
                    Helpers.addToRecent($current.data('id'))
                }
            })
            .on('dblclick doubletap', '.js-media-list-title', (event) => {
                event.preventDefault()

                let data = $(event.currentTarget).data()
                if (data.is_folder === true) {
                    Helpers.resetPagination()
                    _self.FolderService.changeFolderAndAddToRecent(data.id)
                } else {
                    if (!Helpers.isUseInModal()) {
                        ActionsService.handlePreview()
                    } else if (Helpers.getConfigs().request_params.view_in !== 'trash') {
                        let selectedFiles = Helpers.getSelectedFiles()
                        if (Helpers.size(selectedFiles) > 0) {
                            EditorService.editorSelectFile(selectedFiles)
                        }
                    }
                }

                return false
            })
            .on('click', '.js-up-one-level', (event) => {
                event.preventDefault()
                let count = $('.rv-media-breadcrumb .breadcrumb li').length
                $(`.rv-media-breadcrumb .breadcrumb li:nth-child(${count - 1}) a`).trigger('click')
            })
            .on('contextmenu', '.js-context-menu', (event) => {
                if (!$(event.currentTarget).find('input[type=checkbox]').is(':checked')) {
                    $(event.currentTarget).trigger('click')
                }
            })
            .on('click contextmenu', '.rv-media-items', (e) => {
                if (!Helpers.size(e.target.closest('.js-context-menu'))) {
                    $('.rv-media-items input[type="checkbox"]').prop('checked', false)

                    ActionsService.handleDropdown()

                    _self.MediaService.getFileDetails({
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M15 8h.01"></path>
                            <path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z"></path>
                            <path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5"></path>
                            <path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3"></path>
                        </svg>`,
                        nothing_selected: '',
                    })
                }
            })
    }

    changeViewType() {
        let _self = this
        _self.$body
            .off('click', '.js-rv-media-change-view-type button')
            .on('click', '.js-rv-media-change-view-type button', (event) => {
                event.preventDefault()

                let $current = $(event.currentTarget)

                if ($current.hasClass('active')) {
                    return
                }

                $current.closest('.js-rv-media-change-view-type').find('button').removeClass('active')
                $current.addClass('active')

                MediaConfig.request_params.view_type = $current.data('type')

                if ($current.data('type') === 'trash') {
                    $(document).find('.js-insert-to-editor').prop('disabled', true)
                } else {
                    $(document).find('.js-insert-to-editor').prop('disabled', false)
                }

                Helpers.storeConfig()

                if (typeof RV_MEDIA_CONFIG.pagination != 'undefined') {
                    if (typeof RV_MEDIA_CONFIG.pagination.paged != 'undefined') {
                        RV_MEDIA_CONFIG.pagination.paged = 1
                    }
                }

                _self.MediaService.getMedia(true, false)
            })

        $(`.js-rv-media-change-view-type .btn[data-type="${Helpers.getRequestParams().view_type}"]`).trigger('click')

        this.bindIntegrateModalEvents()
    }

    changeFilter() {
        let _self = this
        _self.$body.off('click', '.js-rv-media-change-filter').on('click', '.js-rv-media-change-filter', (event) => {
            event.preventDefault()

            if (!Helpers.isOnAjaxLoading()) {
                let $current = $(event.currentTarget)
                let data = $current.data()

                MediaConfig.request_params[data.type] = data.value

                if (window.rvMedia.options && data.type === 'view_in') {
                    window.rvMedia.options.view_in = data.value
                }

                if (data.type === 'view_in') {
                    MediaConfig.request_params.folder_id = 0
                    if (data.value === 'trash') {
                        $(document).find('.js-insert-to-editor').prop('disabled', true)
                    } else {
                        $(document).find('.js-insert-to-editor').prop('disabled', false)
                    }
                }

                $current.closest('.dropdown').find('.js-rv-media-filter-current').html(`(${$current.html()})`)

                Helpers.storeConfig()
                MediaService.refreshFilter()

                Helpers.resetPagination()
                _self.MediaService.getMedia(true)

                $current.addClass('active')
                $current.siblings().removeClass('active')
            }
        })
    }

    search() {
        let _self = this
        $('.input-search-wrapper input[type="text"]').val(Helpers.getRequestParams().search || '')
        _self.$body.off('submit', '.input-search-wrapper').on('submit', '.input-search-wrapper', (event) => {
            event.preventDefault()
            MediaConfig.request_params.search = $(event.currentTarget).find('input[name="search"]').val()

            Helpers.storeConfig()
            Helpers.resetPagination()
            _self.MediaService.getMedia(true)
        })
    }

    handleActions() {
        let _self = this

        _self.$body
            .off('click', '.rv-media-actions .js-change-action[data-type="refresh"]')
            .on('click', '.rv-media-actions .js-change-action[data-type="refresh"]', (event) => {
                event.preventDefault()

                Helpers.resetPagination()

                let ele_options =
                    typeof window.rvMedia.$el !== 'undefined' ? window.rvMedia.$el.data('rv-media') : undefined
                if (
                    typeof ele_options !== 'undefined' &&
                    ele_options.length > 0 &&
                    typeof ele_options[0].selected_file_id !== 'undefined'
                ) {
                    _self.MediaService.getMedia(true, true)
                } else {
                    _self.MediaService.getMedia(true, false)
                }
            })
            .off('click', '.rv-media-items li.no-items')
            .on('click', '.rv-media-items li.no-items', (event) => {
                event.preventDefault()
                $('.rv-media-header .rv-media-top-header .rv-media-actions .js-dropzone-upload').trigger('click')
            })
            .off('submit', '.form-add-folder')
            .on('submit', '.form-add-folder', (event) => {
                event.preventDefault()
                const $input = $(event.currentTarget).find('input[name="name"]')
                const folderName = $input.val()
                _self.FolderService.create(folderName)
                $input.val('')
                return false
            })
            .off('click', '.js-change-folder')
            .on('click', '.js-change-folder', (event) => {
                event.preventDefault()
                let folderId = $(event.currentTarget).data('folder')
                Helpers.resetPagination()
                _self.FolderService.changeFolderAndAddToRecent(folderId)
            })
            .off('click', '.js-files-action')
            .on('click', '.js-files-action', (event) => {
                event.preventDefault()
                ActionsService.handleGlobalAction($(event.currentTarget).data('action'), () => {
                    Helpers.resetPagination()
                    _self.MediaService.getMedia(true)
                })
            })
            .off('submit', '.form-download-url')
            .on('submit', '.form-download-url', async (event) => {
                event.preventDefault()

                const $el = $('#modal_download_url')
                const $wrapper = $el.find('#download-form-wrapper')
                const $notice = $el.find('#modal-notice').empty()
                const $header = $el.find('.modal-title')
                const $input = $el.find('textarea[name="urls"]').prop('disabled', true)
                const $button = $el.find('[type="submit"]')
                const url = $input.val()
                const remainUrls = []

                Botble.showButtonLoading($button)

                $wrapper.slideUp()

                // start to download
                await _self.DownloadService.download(
                    url,
                    (progress, item, url) => {
                        let $noticeItem = $(`
                        <div class="p-2 text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                                <path d="M12 9h.01"></path>
                                <path d="M11 12h1v4h1"></path>
                            </svg>
                            <span>${item}</span>
                        </div>
                    `)
                        $notice.append($noticeItem).scrollTop($notice[0].scrollHeight)
                        $header.html(
                            `<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                            <path d="M7 11l5 5l5 -5"></path>
                            <path d="M12 4l0 12"></path>
                        </svg>
                        ${$header.data('downloading')} (${progress})`
                        )
                        return (success, message = '') => {
                            if (!success) {
                                remainUrls.push(url)
                            }
                            $noticeItem.find('span').text(`${item}: ${message}`)
                            $noticeItem
                                .attr('class', `py-2 text-${success ? 'success' : 'danger'}`)
                                .find('i')
                                .attr('class', success ? 'icon ti ti-check-circle' : 'icon ti ti-x-circle')
                        }
                    },
                    () => {
                        $wrapper.slideDown()
                        $input.val(remainUrls.join('\n')).prop('disabled', false)
                        $header.html(`<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                            <path d="M7 11l5 5l5 -5"></path>
                            <path d="M12 4l0 12"></path>
                        </svg>
                        ${$header.data('text')}
                    `)
                        Botble.hideButtonLoading($button)
                    }
                )
                return false
            })
    }

    handleModals() {
        let _self = this

        _self.$body.on('show.bs.modal', '#modal_rename_items', () => {
            ActionsService.renderRenameItems()
        })

        _self.$body.on('show.bs.modal', '#modal_alt_text_items', () => {
            ActionsService.renderAltTextItems()
        })

        _self.$body.on('show.bs.modal', '#modal_share_items', () => {
            ActionsService.renderShareItems()
        })

        _self.$body.on('change', '#modal_share_items select[data-bb-value="share-type"]', () => {
            ActionsService.renderShareItems()
        })

        _self.$body.on('show.bs.modal', '#modal_crop_image', () => {
            ActionsService.renderCropImage()
        })

        _self.$body.on('show.bs.modal', '#modal-properties', (event) => {
            if (Helpers.getSelectedItems().length === 1) {
                const $modal = $(event.currentTarget)
                const selected = Helpers.getSelectedItems()[0]
                $modal.find(`input[name="color"][value="${selected.color}"]`).prop('checked', true)
            }
        })

        _self.$body.on('hidden.bs.modal', '#modal_download_url', () => {
            let $el = $('#modal_download_url')
            $el.find('textarea').val('')
            $el.find('#modal-notice').empty()
        })

        _self.$body
            .off('click', '#modal-properties button[type="submit"]')
            .on('click', '#modal-properties button[type="submit"]', (event) => {
                event.preventDefault()

                const $modal = $(event.currentTarget).closest('.modal')

                Botble.showButtonLoading($modal.find('button[type="submit"]'))

                ActionsService.processAction(
                    {
                        action: 'properties',
                        color: $modal.find('input[name="color"]:checked').val(),
                        selected: Helpers.getSelectedItems().map((item) => item.id.toString()),
                    },
                    () => {
                        $modal.modal('hide')
                        Botble.hideButtonLoading($modal.find('button[type="submit"]'))

                        _self.MediaService.getMedia(true)
                    }
                )
            })

        _self.$body
            .off('submit', '#modal_crop_image .form-crop')
            .on('submit', '#modal_crop_image .form-crop', (event) => {
                event.preventDefault()

                const $form = $(event.currentTarget)

                Botble.showButtonLoading($form.find('button[type="submit"]'))

                const imageId = $form.find('input[name="image_id"]').val()
                const cropData = $form.find('input[name="crop_data"]').val()
                ActionsService.processAction(
                    {
                        action: $form.data('action'),
                        imageId,
                        cropData,
                    },
                    (response) => {
                        if (!response.error) {
                            $form.closest('.modal').modal('hide')
                            _self.MediaService.getMedia(true)
                        }

                        Botble.hideButtonLoading($form.find('button[type="submit"]'))
                    }
                )
            })

        _self.$body
            .off('submit', '#modal_rename_items .form-rename')
            .on('submit', '#modal_rename_items .form-rename', (event) => {
                event.preventDefault()
                let items = []
                let $form = $(event.currentTarget)

                $('#modal_rename_items .form-control').each((index, el) => {
                    let $current = $(el)
                    let data = $current.closest('.mb-3').data()
                    data.name = $current.val()
                    items.push(data)
                })

                Botble.showButtonLoading($form.find('button[type="submit"]'))

                ActionsService.processAction(
                    {
                        action: $form.data('action'),
                        selected: items,
                    },
                    (res) => {
                        if (!res.error) {
                            $form.closest('.modal').modal('hide')
                            _self.MediaService.getMedia(true)
                        } else {
                            $('#modal_rename_items .mb-3').each((index, el) => {
                                let $current = $(el)
                                if (Helpers.inArray(res.data, $current.data('id'))) {
                                    $current.addClass('has-error')
                                } else {
                                    $current.removeClass('has-error')
                                }
                            })
                        }

                        Botble.hideButtonLoading($form.find('button[type="submit"]'))
                    }
                )
            })

        _self.$body
            .off('submit', '#modal_alt_text_items .form-alt-text')
            .on('submit', '#modal_alt_text_items .form-alt-text', (event) => {
                event.preventDefault()

                let items = []
                let $form = $(event.currentTarget)

                $('#modal_alt_text_items .form-control').each((index, el) => {
                    let $current = $(el)
                    let data = $current.closest('.mb-3').data()
                    data.alt = $current.val()
                    items.push(data)
                })

                Botble.showButtonLoading($form.find('button[type="submit"]'))

                ActionsService.processAction(
                    {
                        action: $form.data('action'),
                        selected: items,
                    },
                    (res) => {
                        if (!res.error) {
                            $form.closest('.modal').modal('hide')
                            _self.MediaService.getMedia(true)
                        } else {
                            $('#modal_alt_text_items .mb-3').each((index, el) => {
                                let $current = $(el)
                                if (Helpers.inArray(res.data, $current.data('id'))) {
                                    $current.addClass('has-error')
                                } else {
                                    $current.removeClass('has-error')
                                }
                            })
                        }

                        Botble.hideButtonLoading($form.find('button[type="submit"]'))
                    }
                )
            })

        /*Delete files*/
        _self.$body.off('submit', 'form.form-delete-items').on('submit', 'form.form-delete-items', (event) => {
            event.preventDefault()
            let items = []
            let $form = $(event.currentTarget)

            Botble.showButtonLoading($form.find('button[type="submit"]'))

            Helpers.each(Helpers.getSelectedItems(), (value) => {
                items.push({
                    id: value.id,
                    is_folder: value.is_folder,
                })
            })

            ActionsService.processAction(
                {
                    action: $form.data('action'),
                    selected: items,
                    skip_trash: $form.find('input[name="skip_trash"]').is(':checked'),
                },
                (res) => {
                    $form.closest('.modal').modal('hide')
                    if (!res.error) {
                        _self.MediaService.getMedia(true)
                    }

                    $form.find('input[name="skip_trash"]').prop('checked', false)

                    Botble.hideButtonLoading($form.find('button[type="submit"]'))
                }
            )
        })

        /*Empty trash*/
        _self.$body
            .off('submit', '#modal_empty_trash .form-empty-trash')
            .on('submit', '#modal_empty_trash .form-empty-trash', (event) => {
                event.preventDefault()
                let $form = $(event.currentTarget)

                Botble.showButtonLoading($form.find('button[type="submit"]'))

                ActionsService.processAction(
                    {
                        action: $form.data('action'),
                    },
                    () => {
                        $form.closest('.modal').modal('hide')
                        _self.MediaService.getMedia(true)

                        Botble.hideButtonLoading($form.find('button[type="submit"]'))
                    }
                )
            })

        if (Helpers.getRequestParams().view_in === 'trash') {
            $(document).find('.js-insert-to-editor').prop('disabled', true)
        } else {
            $(document).find('.js-insert-to-editor').prop('disabled', false)
        }

        this.bindIntegrateModalEvents()
    }

    checkFileTypeSelect(selectedFiles) {
        if (typeof window.rvMedia.$el !== 'undefined') {
            let firstItem = Helpers.arrayFirst(selectedFiles)
            let ele_options = window.rvMedia.$el.data('rv-media')
            if (
                typeof ele_options !== 'undefined' &&
                typeof ele_options[0] !== 'undefined' &&
                typeof ele_options[0].file_type !== 'undefined' &&
                firstItem !== 'undefined' &&
                firstItem.type !== 'undefined'
            ) {
                if (!ele_options[0].file_type.match(firstItem.type)) {
                    return false
                } else {
                    if (typeof ele_options[0].ext_allowed !== 'undefined' && $.isArray(ele_options[0].ext_allowed)) {
                        if ($.inArray(firstItem.mime_type, ele_options[0].ext_allowed) === -1) {
                            return false
                        }
                    }
                }
            }
        }
        return true
    }

    bindIntegrateModalEvents() {
        let $mainModal = $('#rv_media_modal')
        let _self = this
        $mainModal.off('click', '.js-insert-to-editor').on('click', '.js-insert-to-editor', (event) => {
            event.preventDefault()
            let selectedFiles = Helpers.getSelectedFiles()
            if (Helpers.size(selectedFiles) > 0) {
                window.rvMedia.options.onSelectFiles(selectedFiles, window.rvMedia.$el)
                if (_self.checkFileTypeSelect(selectedFiles)) {
                    $mainModal.find('.btn-close').trigger('click')
                }
            }
        })

        $mainModal
            .off('dblclick doubletap', '.js-media-list-title[data-context="file"]')
            .on('dblclick doubletap', '.js-media-list-title[data-context="file"]', (event) => {
                event.preventDefault()
                if (Helpers.getConfigs().request_params.view_in !== 'trash') {
                    let selectedFiles = Helpers.getSelectedFiles()
                    if (Helpers.size(selectedFiles) > 0) {
                        window.rvMedia.options.onSelectFiles(selectedFiles, window.rvMedia.$el)
                        if (_self.checkFileTypeSelect(selectedFiles)) {
                            $mainModal.find('.btn-close').trigger('click')
                        }
                    }
                } else {
                    ActionsService.handlePreview()
                }
            })
    }

    // Scroll get more media
    scrollGetMore() {
        let _self = this
        let $mediaList = $('.rv-media-main .rv-media-items')

        // Handle both mouse wheel and touch scroll events
        $mediaList.on('wheel scroll', function (e) {
            let $target = $(e.currentTarget)
            let scrollHeight = $target[0].scrollHeight
            let scrollTop = $target.scrollTop()
            let innerHeight = $target.innerHeight()

            let threshold = $target.closest('.media-modal').length > 0 ? 450 : 150
            let loadMore = scrollTop + innerHeight >= scrollHeight - threshold

            if (loadMore && RV_MEDIA_CONFIG.pagination?.has_more) {
                _self.MediaService.getMedia(false, false, true)
            }
        })
    }
}

$(() => {
    window.rvMedia = window.rvMedia || {}

    new MediaManagement().init()
})
