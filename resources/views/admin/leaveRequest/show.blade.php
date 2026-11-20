<div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold mb-0">{{ __('index.leave_reason') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container">
                   <div class="row">
                        <div class="col-lg-12 mb-2 pb-2 border-bottom">
                            <label for="exampleFormControlSelect1" class="form-label fw-bold">{{ __('index.referred_by') }}</label>
                            <p class="form-control border-0 p-0 fst-italic" style="height:inherit"  id="referredBy"></p>
                        </div>

                    
                        <div class="col-lg-12 mb-2 pb-2 border-bottom">
                            <label for="exampleFormControlSelect1" class="form-label fw-bold">{{ __('index.leave_reason') }}</label>
                            <p class="form-control border-0 p-0 fst-italic" style="height:inherit" id="description"></p>
                        </div>

                    
                        <div class="col-lg-12">
                            <label for="exampleFormControlSelect1" class="form-label fw-bold">{{ __('index.admin_remark') }}</label>
                            <p class="form-control border-0 p-0 fst-italic" style="height:inherit"  id="adminRemark" ></p>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer py-2 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-primary btn-sm quick-print-leave" id="btnModalPrintLeave" data-print-url="" title="{{ __('index.print') ?? 'Print' }}">
                    <i class="link-icon me-1" data-feather="printer"></i> {{ __('index.print') ?? 'Print' }}
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    {{ __('index.close') ?? 'Close' }}
                </button>
            </div>
        </div>
    </div>
</div>
