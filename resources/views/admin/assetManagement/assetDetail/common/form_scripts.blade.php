<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>


<script>
    $('document').ready(function(){

        const selectLanguage = {
            noResults: () => @json(__('index.asset_select_no_results')),
            searching: () => @json(__('index.asset_select_searching')),
            errorLoading: () => @json(__('index.asset_select_error')),
            removeAllItems: () => @json(__('index.asset_select_clear'))
        };
        $("select#branch_id, #type").select2({width: '100%', language: selectLanguage});
        $("#asset_set_by").select2({
            width: '100%',
            placeholder: @json(__('index.asset_select_setup_person')),
            allowClear: true,
            minimumResultsForSearch: 0,
            language: selectLanguage
        });

        $('#asset_set_by').on('select2:open', function () {
            const search = document.querySelector('.select2-container--open .select2-search__field');
            if (search) {
                search.placeholder = @json(__('index.asset_select_search'));
                search.setAttribute('aria-label', @json(__('index.asset_select_search')));
                search.focus();
            }
        });

        @if(app()->getLocale() === 'km')
            tinymce.addI18n('km', @json(__('index.asset_editor_i18n')));
        @endif
        tinymce.init({
            selector: '#tinymceExample',
            language: @json(app()->getLocale() === 'km' ? 'km' : 'en'),
            height: window.innerWidth < 576 ? 220 : 280,
            toolbar_mode: 'sliding',
            min_height: 200,
            menubar: false,
            branding: false,
            statusbar: false,
            contextmenu: false,
            plugins: 'lists',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist',
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }'
        });

        let warranty = $('#warranty_available').val();
        $('#warrantyDateRequired').prop('hidden', warranty != 1);
        if (warranty == 1 ) {
            $('#warranty_end_date').attr('required', 'true');
        } else {
            $('#warranty_end_date').removeAttr('required');
        }

        $('#warranty_available').change(function(event){
            event.preventDefault()
            let warrantyAvailable = $(this).val();
            $('#warrantyDateRequired').prop('hidden', warrantyAvailable != 1);
            if(warrantyAvailable == 0){
                $('#warranty_end_date').val('');
               $('#warranty_end_date').removeAttr('required')
           }else{
               $('#warranty_end_date').attr('required','true')
           }
        });


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });





    });

</script>
@php
    $assetTypeConfig = [
        'url' => url('admin/get-branch-asset-data'),
        'initialType' => (string) old('type_id', $assetDetail->type_id ?? ''),
        'assetId' => $assetDetail->id ?? null,
        'placeholder' => __('index.select_asset_type'),
        'empty' => __('index.asset_type_not_found'),
        'error' => __('index.error_loading_asset_types'),
        'loading' => __('index.asset_type_loading'),
    ];
@endphp
<script type="application/json" id="assetTypeConfig">@json($assetTypeConfig)</script>
<script src="{{ asset('assets/js/asset-form-types.js') }}?v={{ filemtime(public_path('assets/js/asset-form-types.js')) }}"></script>

<script src="{{ asset('assets/js/asset-images.js') }}?v={{ filemtime(public_path('assets/js/asset-images.js')) }}"></script>

@include('admin.assetManagement.assetDetail.common.image-viewer')

<script>
    document.querySelectorAll('[data-asset-secret-toggle]').forEach(button => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.assetSecretToggle);
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            button.textContent = visible ? @json(__('index.link_hide_password')) : @json(__('index.link_show_password'));
            button.setAttribute('aria-pressed', String(visible));
        });
    });
    document.querySelectorAll('[data-asset-secret-clear]').forEach(checkbox => {
        const update = () => {
            document.getElementById(checkbox.dataset.assetSecretClear).disabled = checkbox.checked;
            document.querySelector('[data-asset-secret-toggle="' + checkbox.dataset.assetSecretClear + '"]').disabled = checkbox.checked;
        };
        checkbox.addEventListener('change', update); update();
    });
</script>
