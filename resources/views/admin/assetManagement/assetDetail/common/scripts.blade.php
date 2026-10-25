<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $('document').ready(function(){

        $("#branch_id").select2({width: '100%'});
        $("#type").select2({width: '100%'});
        $("#assigned_to").select2({});


        $('#image').change(function(){
            const input = document.getElementById('image');
            const preview = document.getElementById('image-preview');
            const file = input.files[0];
            const reader = new FileReader();
            reader.addEventListener('load', function() {
                preview.src = reader.result;
            });
            reader.readAsDataURL(file);
            $('#image-preview').removeClass('d-none')

        })





        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('body').on('click', '.delete', function (event) {
            event.preventDefault();
            let title = $(this).data('title');
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.delete_tada_confirm', ['title' => ':name']) }}'.replace(':name', title),
                showDenyButton: true,
                confirmButtonText: `@lang('index.yes')`,
                denyButtonText: `@lang('index.no')`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: '@lang('index.change_availability_status') ',
                showDenyButton: true,
                confirmButtonText: `@lang('index.yes')`,
                denyButtonText: `@lang('index.no')`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }else if (result.isDenied) {
                    (status === 0)? $(this).prop('checked', true) :  $(this).prop('checked', false)
                }
            })
        })


    });

    $(document).ready(function () {
        // Define variables
        const branchId = "{{ $filterParameters['branch_id'] ?? '' }}";
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        const filterTypeId = "{{ $filterParameters['type_id'] ?? '' }}";

        const loadClientAndUsers = async (selectedBranchId, typeIdToSelect = filterTypeId) => {
            if (!selectedBranchId) return;

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/get-branch-asset-data') }}/${selectedBranchId}`,
                });

                // Debugging: Log the response and typeIdToSelect
                console.log('Branch ID:', selectedBranchId);
                console.log('Type ID to Select:', typeIdToSelect);
                console.log('Response Types:', response.types);

                // Clear existing options
                $('#type').empty();

                // Populate types
                if (response.types && response.types.length > 0) {
                    // Add default "Select" option only if no type is pre-selected
                    if (!typeIdToSelect) {
                        $('#type').append('<option value="" disabled selected>{{ __('index.select_asset_type') }}</option>');
                    }

                    response.types.forEach(type => {
                        // Ensure type.id and typeIdToSelect are compared as strings
                        const isSelected = String(type.id) === String(typeIdToSelect) ? 'selected' : '';
                        $('#type').append(
                            `<option value="${type.id}" ${isSelected}>${type.name}</option>`
                        );
                    });
                } else {
                    $('#type').append('<option disabled>{{ __("index.asset_type_not_found") }}</option>');
                }

                // Double-check selection after population
                if (typeIdToSelect) {
                    $('#type').val(typeIdToSelect); // Force selection
                }
            } catch (error) {
                console.error('Error loading types:', error);
                $('#type').empty().append('<option disabled>{{ __("index.error_loading_asset_types") }}</option>');
            }
        };

        const initializeDropdowns = async () => {
            let selectedBranchId;

            if (isAdmin) {
                selectedBranchId = $('#branch_id').val() || branchId;
                $('#branch_id').change(async () => {
                    const newBranchId = $('#branch_id').val();
                    await loadClientAndUsers(newBranchId, filterTypeId);
                    $('#assigned_to').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');
                });
            } else {
                selectedBranchId = defaultBranchId;
            }

            if (selectedBranchId) {
                await loadClientAndUsers(selectedBranchId, filterTypeId);
            }
        };

        // Ensure initialization happens after DOM is ready
        initializeDropdowns();
    });


</script>
<script src="{{ asset('assets/js/asset-assignment.js') }}?v={{ filemtime(public_path('assets/js/asset-assignment.js')) }}"></script>

<script>
    document.querySelectorAll('[data-asset-actions]').forEach(button => {
        new bootstrap.Dropdown(button, {
            popperConfig: defaults => ({...defaults, strategy: 'fixed'}),
        });
    });
</script>
