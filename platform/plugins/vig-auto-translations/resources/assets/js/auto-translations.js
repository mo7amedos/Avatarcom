$(() => {
    $(document).on('click', '.btn-get-auto-translate', function(e) {
        e.preventDefault()
        let $this = $(e.currentTarget)
        let $tr = $this.closest('tr')
        $tr.find('a.editable').each(function(i, et) {
            let $et = $(et)
            $.ajax({
                url: route('vig-auto-translations.auto-translate'),
                data: Object.fromEntries(Object.entries($et.data()).filter(([key, value]) => typeof (value) != 'object')),
                method: 'get',
                beforeSend: () => {
                    $this.addClass('button-loading')
                },
                success: (res) => {
                    if (res.error) {
                        Botble.showError(res.message)
                    } else {
                        let value = $et.editable('getValue', true)
                        let $reset = $this.closest('td').find('.btn-reset-auto-translate')
                        if (value != res.data[$et.data('locale')]) {
                            $et.editable('setValue', res.data[$et.data('locale')]).editable('submit')
                            $reset.removeClass('d-none')
                        } else {
                            $reset.addClass('d-none')
                        }
                    }
                },
                error: (res) => {
                    Botble.handleError(res)
                },
                complete: () => {
                    $this.removeClass('button-loading')
                },
            })
        })
    })

    $(document).on('click', '.btn-reset-auto-translate', function(e) {
        let $this = $(e.currentTarget)
        let $tr = $this.closest('tr')
        $tr.find('a.editable').each(function(i, et) {
            let $et = $(et)
            $et.editable('setValue', $et.data('name')).editable('submit')

            $this.addClass('d-none')
        })
    })
})
