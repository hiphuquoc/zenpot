@php
    $dataShipPort 		= new \Illuminate\Database\Eloquent\Collection;
@endphp
<div class="bookFormSortShip">
    <div class="bookFormSortShip_column">

        <div class="bookFormSortShip_column_item">
            <div class="inputWithIconBetween">
                <div class="inputWithIconBetween_item inputWithLabelInside location">
                    <label for="js_loadShipLocationByShipDeparture_element">Điểm đi</label>
                    <select id="js_loadShipLocationByShipDeparture_element" class="select2 form-select select2-hidden-accessible" name="ship_port_departure_id" onchange="loadShipLocationByShipDeparture(this, 'js_loadShipLocationByShipDeparture_idWrite');" tabindex="-1" aria-hidden="true">
                        @foreach($dataShipPort as $port)
                            @php
                                $selected	= null;
                                /* kiểm tra cho trang ship_info */
                                if(!empty($item->portDeparture->name)&&$item->portDeparture->name==$port->name) $selected = 'selected';
                                /* kiểm tra cho trang ship_location */
                                if(!empty($item->ships[0]->portDeparture->name)&&$item->ships[0]->portDeparture->name==$port->name) $selected = 'selected';
                                $portName 	= \App\Helpers\Build::buildFullShipPort($port);
                            @endphp
                            <option value="{{ $port->id }}" {{ $selected }}>{{ $portName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="inputWithIconBetween_icon">
                    <img src="/images/main/svg/icon-round.svg" alt="đặt vé tàu cao tốc" title="đặt vé tàu cao tốc" />
                </div>
                <div class="inputWithIconBetween_item inputWithLabelInside location">
                    <label for="js_loadShipLocationByShipDeparture_idWrite">Điểm đến</label>
                    <select id="js_loadShipLocationByShipDeparture_idWrite" class="select2 form-select select2-hidden-accessible" name="ship_port_location_id" tabindex="-1" aria-hidden="true">
                    </select>
                </div>
            </div>
        </div>

        <div class="bookFormSortShip_column_item">
            <div class="bookFormSortShip_input_item">
                <div class="inputWithLabelInside date">
                    <label for="input_date_ship_1">Ngày khởi hành</label>
                    <input type="text" class="form-control flatpickr-basic flatpickr-input active" id="input_date_ship_1" name="date_1" value="{{ date('Y-m-d', time() + 86400) }}" aria-label="Ngày đi tàu cao tốc" readonly="readonly" required>
                </div>
            </div>
        </div>
    </div>
    <div class="bookFormSortShip_column">
        <div class="bookFormSortShip_column_item">
            <div class="inputWithLabelInside peopleGroup inputWithForm">
                <label for="bookFormSort_date">Số hành khách</label>
                <input type="text" id="js_setValueQuantityShip_idWrite" class="form-control inputWithForm_input" name="quantity" value="1 Người lớn, 0 Trẻ em, 0 Cao tuổi" readonly="readonly" aria-label="Số khách đặt vé tàu cao tốc" required>
                <div class="inputWithForm_form">
                    <div class="formBox">
                        <div class="formBox_labelOneRow">
                            <div class="formBox_labelOneRow_item">
                                <div class="labelWithIcon">
                                    <div class="labelWithIcon_icon adult"></div>
                                    <div class="labelWithIcon_label">
                                        Người lớn (Năm sinh từ {{ date('Y', time()) - 12 }} - {{ date('Y', time()) - 59 }})
                                    </div>
                                </div>
                                <div class="inputNumberCustom"> 
                                    <div class="inputNumberCustom_button" onClick="changeValueInputShip('js_changeValueInputShip_input_nguoilon', 'minus');">
                                        <i class="fa-solid fa-minus"></i>
                                    </div>
                                    <input id="js_changeValueInputShip_input_nguoilon" class="inputNumberCustom_input" type="number" name="adult_ship" value="1" aria-label="Số người lớn đặt vé tàu cao tốc" onkeyup="setValueQuantityShip()" />
                                    <div class="inputNumberCustom_button" onClick="changeValueInputShip('js_changeValueInputShip_input_nguoilon', 'plus');">
                                        <i class="fa-solid fa-plus"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="formBox_labelOneRow_item">
                                <div class="labelWithIcon">
                                    <div class="labelWithIcon_icon children"></div>
                                    <div class="labelWithIcon_label">
                                        Trẻ em (Năm sinh từ {{ date('Y', time()) - 6 }} - {{ date('Y', time()) - 11 }})
                                    </div>
                                </div>
                                <div class="inputNumberCustom"> 
                                    <div class="inputNumberCustom_button" onClick="changeValueInputShip('js_changeValueInputShip_input_treem', 'minus');">
                                        <i class="fa-solid fa-minus"></i>
                                    </div>
                                    <input id="js_changeValueInputShip_input_treem" class="inputNumberCustom_input" type="number" name="child_ship" value="0" aria-label="Số trẻ em đặt vé tàu cao tốc" onkeyup="setValueQuantityShip()" />
                                    <div class="inputNumberCustom_button" onClick="changeValueInputShip('js_changeValueInputShip_input_treem', 'plus');">
                                        <i class="fa-solid fa-plus"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="formBox_labelOneRow_item">
                                <div class="labelWithIcon">
                                    <div class="labelWithIcon_icon adult"></div>
                                    <div class="labelWithIcon_label">
                                        Cao tuổi (Năm sinh từ {{ date('Y', time()) - 60 }})
                                    </div>
                                </div>
                                <div class="inputNumberCustom"> 
                                    <div class="inputNumberCustom_button" onClick="changeValueInputShip('js_changeValueInputShip_input_caotuoi', 'minus');">
                                        <i class="fa-solid fa-minus"></i>
                                    </div>
                                    <input id="js_changeValueInputShip_input_caotuoi" class="inputNumberCustom_input" type="number" name="old_ship" value="0" aria-label="Số người cao tuổi đặt vé tàu cao tốc" onkeyup="setValueQuantityShip()" />
                                    <div class="inputNumberCustom_button" onClick="changeValueInputShip('js_changeValueInputShip_input_caotuoi', 'plus');">
                                        <i class="fa-solid fa-plus"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bookFormSortShip_column_item button">
            <div class="buttonSecondary" onClick="submitForm('shipBookingSort');">
                <i class="fa-solid fa-magnifying-glass"></i>Tìm chuyến tàu
            </div>
        </div>
    </div>
</div>

@push('scripts-custom')
    <script type="text/javascript">
    
    </script>
@endpush