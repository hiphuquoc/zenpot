@push('head-custom')
    <link rel="stylesheet" type="text/css" href="{{ asset('sources/admin/app-assets/vendors/css/vendors.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('sources/admin/app-assets/vendors/css/pickers/pickadate/pickadate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('sources/admin/app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('sources/admin/app-assets/css/plugins/forms/pickers/form-flat-pickr.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('sources/admin/app-assets/css/plugins/forms/pickers/form-pickadate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('sources/admin/app-assets/vendors/css/forms/select/select2.min.css') }}">
@endpush

<div class="container">
	<div class="bookFormSort" onClick="hideShowAround();">
		<div class="bookFormSort_head">
			<div {{ !empty($active)&&$active=='ship' ? 'class=active' : null }} data-tab="shipBookingForm" onClick="changeTab(this);">
				<div class="show-767 maxLine_1"><i class="fa-solid fa-ship"></i>Tàu</div>
				<div class="hide-767 maxLine_1"><i class="fa-solid fa-ship"></i>Tàu cao tốc</div>
			</div>
		</div>
		<div class="bookFormSort_body">
			<!-- Ship booking form -->
			<div id="shipBookingForm" {{ !empty($active)&&$active!='ship' ? 'style=display:none;' : null }}>
				<form id="shipBookingSort" method="GET" action="#">
					@include('main.form.sortBooking.ship', compact('item'))
				</form>
			</div>
		</div>
	</div>
</div>

@push('scripts-custom')
	<!-- BEGIN: Page Vendor JS-->
    <script src="{{ asset('sources/admin/app-assets/vendors/js/pickers/pickadate/picker.js') }}"></script>
    <script src="{{ asset('sources/admin/app-assets/vendors/js/pickers/pickadate/picker.date.js') }}"></script>
    <script src="{{ asset('sources/admin/app-assets/vendors/js/pickers/pickadate/picker.time.js') }}"></script>
    <script src="{{ asset('sources/admin/app-assets/vendors/js/pickers/pickadate/legacy.js') }}"></script>
    <script src="{{ asset('sources/admin/app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js') }}"></script>
	<!-- ===== -->
    <script src="{{ asset('sources/admin/app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('sources/admin/app-assets/js/scripts/forms/pickers/form-pickers.js') }}"></script>
    <script src="{{ asset('sources/admin/app-assets/vendors/js/forms/select/select2.full.min.js') }}"></script>
    <script src="{{ asset('sources/admin/app-assets/js/scripts/forms/form-select2.min.js') }}"></script>
    <script type="text/javascript">

		$(document).ready(function(){
			loadShipLocationByShipDeparture($('#js_loadShipLocationByShipDeparture_element'), 'js_loadShipLocationByShipDeparture_idWrite', '{{ $item->portLocation->name ?? $item->ships[0]->portLocation->name ?? null }}');
		});

		function submitForm(idForm){
            // event.preventDefault();
            $('#'+idForm).submit();
        }

        function hideShowAround(action = 'on'){
			const elemt = $('#js_hideShowAround');
			if(elemt.length==0){
				$('<div id="js_hideShowAround" style="width:100%;height:100%;position:fixed;background-color:rgb(35, 42, 49);opacity: 0.8;top:0;left:0;z-index:100;" onClick="hideShowAround(\'off\');"></div>').appendTo('body');
			}else {
				if(action=='off') elemt.remove();
			}
		}

		function changeTab(element){
			/* active button */
			$(element).parent().children().each(function(){
				$(this).removeClass('active');
			});
			$(element).addClass('active');
			/* xử lý tab content */
			const idContent 		= $(element).data('tab');
			const elementContent 	= $('#'+idContent);
			/* ẩn tất cả tab */
			elementContent.parent().children().each(function(){
				$(this).css('display', 'none');
			});
			/* bật tab được chọn */
			elementContent.css('display', 'block');
		}
    </script>
@endpush