<div class="offcanvas offcanvas-end eOffcanvas" data-bs-scroll="true" tabindex="-1" id="offcanvasScrollingRightBS" aria-labelledby="offcanvasScrollingRightLabel">
  <div class="offcanvas-header">
    <div class="eDisplay-5" id="offcanvasRightLabel">{{ get_phrase('Loading...') }}</div>
    <a href="#" class="offcanvas-btn"
      data-bs-dismiss="offcanvas" aria-label="Close">
      <svg xmlns='http://www.w3.org/2000/svg'
        viewBox='0 0 16 16'>
        <path
          d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z' />
      </svg>
    </a>
  </div>
  <div class="offcanvas-body" id="offcanvasScrollingRightLabel">
    {{ get_phrase('Loading...') }}
  </div>
</div>
<script type="text/javascript">

  "use strict";

  function rightModal(url, title){
    var myOffcanvas = document.getElementById('offcanvasScrollingRightBS')
      var bsOffcanvas = new bootstrap.Offcanvas(myOffcanvas);
      bsOffcanvas.show();
      $('#offcanvasRightLabel').html(title);

      $.ajax({
        type:"get",
        url: url,
        success: function(response){
          $("#offcanvasScrollingRightLabel").html(response);
        }
      });
  }
</script>


<div class="modal fade" id="confirmModal" aria-hidden="true" aria-labelledby="confirmModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content py-4">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title w-100 text-center">{{ get_phrase('Heads up') }}</h5>
      </div>
      <div class="modal-body text-center">{{ get_phrase('Are you sure') }}?</div>
      <div class="modal-footer d-block border-top-0 text-center">
        <button type="button" class="btn btn-secondary modal-btn-close" data-bs-dismiss="modal" aria-label="Close">{{ get_phrase('Back') }}</button>
        <a href="javascript:;" id="continue_btn" class="btn btn-danger">{{ get_phrase('Continue') }}</a>
      </div>
    </div>
  </div>
</div>


<div class="modal eModal fade" id="confirmSweetAlerts" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div
    class="modal-dialog modal-dialog-centered sweet-alerts text-sweet-alerts">
    <div class="modal-content">
      <div class="modal-body">
        <div class="icon icon-confirm">
          <svg xmlns="http://www.w3.org/2000/svg" height="48"
            width="48">
            <path
              d="M22.5 29V10H25.5V29ZM22.5 38V35H25.5V38Z" />
          </svg>
        </div>
        <p>{{ get_phrase('Are you sure?') }}</p>
        <p class="focus-text">{{ get_phrase('You won\'t able to revert this!') }}</p>
        <div class="confirmBtn">
          <a href="javascript:;" id="confirmBtn" class="eBtn eBtn-green">
            <button type="button" id="confirmBtn" class="eBtn eBtn-green">{{ get_phrase('Yes') }}</button>
          </a>
          <button type="button" class="eBtn eBtn-red"
            data-bs-dismiss="modal">{{ get_phrase('Cancel') }}</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">

  "use strict";

  function confirmModal(deleteUrl, callBackFunction){
    var confirmModal = new bootstrap.Modal(document.getElementById('confirmSweetAlerts'), {
      keyboard: false
    });
    confirmModal.show();

    if(callBackFunction == 'undefined')
    {
      $('#confirmBtn').attr('href', deleteUrl);
    }
    else if(callBackFunction == 'ajax_delete')
    {
        $('#confirmBtn').attr('onclick',deleteUrl);
    }
    else{
      $('#confirmBtn').attr('onclick', "deleteDataUsingAjax('"+deleteUrl+"', "+callBackFunction+");");
    }
  }

  function deleteDataUsingAjax(url, callBackFunction){
    
    $.ajax({
      type:"POST",
      url: url,
      success: function(response){
        callBackFunction();

        if(response){
          var jsonResponse = JSON.parse(response);
          if(jsonResponse.status == 'error'){
              error_message(jsonResponse.message);
          }else{
            if(jsonResponse.redirect){
                window.location.replace(jsonResponse.redirect);
            }else{
                success_message(jsonResponse.message);
            }
          }
        }
      }
    });
  }

</script>



<script type="text/javascript">

  "use strict";

var callBackFunction;
var callBackFunctionForGenericConfirmationModal;
function largeModal(url, header)
{
  jQuery('#large-modal').modal('show', {backdrop: 'true'});
  // SHOW AJAX RESPONSE ON REQUEST SUCCESS
  $.ajax({
    type: 'get',
    url: url,
    success: function(response)
    {
      jQuery('#large-modal .modal-body').html(response);
      jQuery('#large-modal .modal-title').html(header);
    }
  });
}
</script>


<!--  Large Modal -->
<div class="modal fade" id="large-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header d-print-none">
        <h4 class="modal-title" id="myLargeModalLabel"></h4>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">

      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<script>
  function showAjaxModal(url, header)
  {
      // SHOWING AJAX PRELOADER IMAGE
      jQuery('#scrollable-modal .modal-body').html('<div style="text-align:center;margin-top:200px;"><img style="width: 100px; opacity: 0.4; " src="{{ asset('assets/images/straight-loader.gif') }}" /></div>');
      jQuery('#scrollable-modal .modal-title').html('...');
      // LOADING THE AJAX MODAL
      jQuery('#scrollable-modal').modal('show', {backdrop: 'true'});

      // SHOW AJAX RESPONSE ON REQUEST SUCCESS
      $.ajax({
          url: url,
          success: function(response)
          {
              jQuery('#scrollable-modal .modal-body').html(response);
              jQuery('#scrollable-modal .modal-title').html(header);
          }
      });
  }
</script>
<!-- Scrollable modal -->
<div class="modal fade" id="scrollable-modal" tabindex="-1" role="dialog" aria-labelledby="scrollableModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="scrollableModalTitle">Modal title</h5>
              <a href="#" class="offcanvas-btn"
                data-bs-dismiss="modal" aria-label="Close">
                <svg xmlns='http://www.w3.org/2000/svg'
                  viewBox='0 0 16 16'>
                  <path
                    d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z' />
                </svg>
              </a>
          </div>
          <div class="modal-body ml-2 mr-2">

          </div>
          <div class="modal-footer">
              <button class="eBtn eBtn-red" data-bs-dismiss="modal"><?php echo get_phrase("Close"); ?></button>
          </div>
      </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>
