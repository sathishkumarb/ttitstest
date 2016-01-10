var copyArray = [];
function hightlight() {
    jQuery('.mapping').maphilight({
        strokeColor: '4F95EA',
        alwaysOn: true,
        fillColor: '365E71',
        fillOpacity: 0.2,
        shadow: true,
        shadowColor: '000000',
        shadowRadius: 5,
        shadowOpacity: 0.6,
        shadowPosition: 'outside'
    });
}
jQuery(document).ready(function (e) {
    var map = {'imgName': '', 'areas': []};//map object
    var areas = {};//area object
    var seats = [];//seats object
    //modalOverlay jQuery
    var active = false, selections = [];
    var dialogBox, zoneCount, uploadStatus, uploadMap, click = false, clickedID, c = false, newName = "", boxName, boxDtcmName, boxPrice, length = 0, boxCount, valid = false, coordsText, imageName, boxType;
    //init mapping
    jQuery('.mapping').maphilight({
        strokeColor: '4F95EA',
        alwaysOn: true,
        fillColor: '365E71',
        fillOpacity: 0.2,
        shadow: true,
        shadowColor: '000000',
        shadowRadius: 5,
        shadowOpacity: 0.6,
        shadowPosition: 'outside'
    });
    //undo last coordinate
    function undoLastCoordinates(newName) {
        if (newName !== "") {
            var str = jQuery('map').find('.area_' + newName).attr('coords');
            if (typeof str === 'undefined') {
                newName = "";
                c = false;
                jQuery('#coordsText').val("");
                jQuery('#drawCanvas').find('img.dot:last').remove();
                hightlight();
            } else {
                var countKomma = str.split(',').length;
                if (countKomma === 2) {
                    jQuery('map').find('.area_' + newName).remove();
                    newName = "";
                    c = false;
                    jQuery('#coordsText').val("");
                    jQuery('#drawCanvas').find('img.dot:last').remove();
                    hightlight();
                }
                var coordsText = str.substring(0, str.lastIndexOf(',', str.lastIndexOf(',') - 1));
                var coordsDots = str.substring(0, str.lastIndexOf(',', str.lastIndexOf(',') - 1));
                jQuery('#drawCanvas').find('img.dot:last').remove();
                jQuery('map').find('.area_' + newName).attr('coords', coordsText);
                jQuery('#coordsText').val(coordsText);
                jQuery('#coordsDots').val(coordsDots);
                hightlight();
            }
        }
    }
    //set coordinate when click
    function setCoordinates(x, y) {
        var value = jQuery('#coordsText').val();
        var countKomma = value.split(',').length;
        var shape = (countKomma <= 4) ? 'rect' : 'poly';
        jQuery('#drawCanvas').append('<img id="' + x + '-' + y + '" class="dot" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAMAAAADCAYAAABWKLW/AAAABGdBTUEAALGPC/xhBQAAABh0RVh0U29mdHdhcmUAUGFpbnQuTkVUIHYzLjM2qefiJQAAACFJREFUGFdj9J/6KomBgUEYiN8yADmlQPwfRIM4SVCBJAAiRREoec4ImAAAAABJRU5ErkJggg==" style="left: ' + (x - 1) + 'px; top: ' + (y - 1) + 'px;position: absolute;" />');
        if (jQuery("#areaName").val() === newName) {
            jQuery("map").find(".area_" + newName).remove();
        }
        if (countKomma >= 4) {
            var html = '<area shape="' + shape + '" id="area" class="area_' + newName + '" coords="' + value + '" title="' + newName + '">';
            jQuery('map').append(html);
        }
        hightlight();
    }
    //valid form area
    function validAreaForm() {
        boxName = jQuery.trim(jQuery("#dialogAreaForm #boxName").val()).replace(/ /g, "_");
        boxPrice = jQuery.trim(jQuery("#dialogAreaForm #boxPrice").val()).replace(/ /g, "_");
        boxCount = jQuery.trim(jQuery("#dialogAreaForm #boxCount").val()).replace(/ /g, "_");
        if (boxName && boxPrice && boxCount !== "") {
            coordsText = jQuery("#coordsText").val();
            imageName = jQuery("#imageName").val();
            boxType = jQuery("map").find(".area_tmp_area").attr('shape');
            return true;
        }
        return false;
    }
    //creating a new box
    function createNewBox() {
        jQuery('#tmpAreaForm').trigger('reset');
        jQuery('#dialogAreaForm').trigger('reset');
        jQuery('#drawCanvas').find('img.dot').remove();//remove all the tmp dots
        jQuery('#areaName').val('tmp_area');
        c = true;
        click = true;
        newName = jQuery('#areaName').val();
        zoneCount = jQuery('area.area').length;
        jQuery('#zoneCount').val(zoneCount).trigger("change");
    }
    //add area to a array
    function addAreaToMapObj(zoneID, zoneTitle, zoneDtcm, zonePrice, zoneType, zoneCount) {
        var nameID = zoneID.split('_')[1];
        jQuery('#zoneMaps').append('<div id="map_' + zoneID + '" class="btn-group btn-block" data-upload="false">\n\
                    <label>Upload ' + nameID + ' seats</label>\n\
                    <button id="uploadMap" class="btn btn-primary" data-id="' + zoneID + '" data-count="' + zoneCount + '" data-toggle="modal" data-target="#seatAreaModal">\n\
                        <span class="glyphicon glyphicon-upload"></span> Upload\n\
                    </button>\n\
                    <button id="deleteMap" class="btn btn-primary" data-id="' + zoneID + '">\n\
                        <span class="glyphicon glyphicon-remove"></span>\n\
                    </button></div>');
        var zoneCoords = jQuery('#drawCanvas map area#' + zoneID).attr('coords');
        areas = new Object();
        areas.zoneID = zoneID;
        areas.zoneTitle = zoneTitle;
        areas.zonePrice = zonePrice;
        areas.zoneDtcm = zoneDtcm;
        areas.zoneType = zoneType;
        areas.zoneCount = zoneCount;
        areas.zoneMaps = zoneCoords;
        areas.zoneSeats = [];
        map.areas.push(areas);
    }
    //edit area from the array
    function editAreaFromMapObj(clickedID, zoneID, zoneTitle, zoneDtcm, zonePrice, zoneType, zoneCount) {
        var obj = map.areas;
        var nameID = zoneID.split('_')[1];
        var uploadData = jQuery('#zoneMaps').find('#map_' + clickedID).attr('data-upload');
        if (uploadData === 'false') {
            jQuery('#zoneMaps').find('#map_' + clickedID).remove();
            jQuery('#zoneMaps').append('<div id="map_' + zoneID + '" class="btn-group btn-block" data-upload="false">\n\
                    <label>Upload ' + nameID + ' seats</label>\n\
                    <button id="uploadMap" class="btn btn-primary" data-id="' + zoneID + '" data-count="' + zoneCount + '" data-toggle="modal" data-target="#seatAreaModal">\n\
                        <span class="glyphicon glyphicon-upload"></span> Upload\n\
                    </button>\n\
                    <button id="deleteMap" class="btn btn-primary" data-id="' + zoneID + '">\n\
                        <span class="glyphicon glyphicon-remove"></span>\n\
                    </button></div>');
        }
        jQuery.each(obj, function (key, value) {
            if (value.zoneID === clickedID) {
                value.zoneID = zoneID;
                value.zoneTitle = zoneTitle;
                value.zoneDtcm = zoneDtcm;
                value.zonePrice = zonePrice;
                value.zoneCount = zoneCount;
            }
        });
    }
    //delete area from the array
    function deleteAreaFromMapObj(clickedID) {
        var obj = map.areas;
        map.areas = obj.filter(function (e) {
            return e.zoneID !== clickedID;
        });
        jQuery('#zoneMaps #map_' + clickedID).remove();
        jQuery('map').find('area#' + clickedID).remove();
        hightlight();
        createNewBox();
        click = true;
    }
    //remove all from array
    function removeAreasFromMapObj() {
        map.areas = [];
    }
    //dialog box form
    dialogBox = jQuery("#dialogArea").dialog({
        dialogClass: "noClose",
        autoOpen: false,
        closeOnEscape: false,
        draggable: false,
        buttons: [
            {
                text: "SAVE",
                click: function () {
                    valid = validAreaForm();
                    if (valid) {
                        jQuery(this).dialog("close");
                        changeZoneArea();
                    } else {
                        alert("please fill the form");
                    }
                }
            },
            {
                text: "DELETE",
                click: function () {
                    jQuery(this).dialog("close");
                    deleteAreaFromMapObj(clickedID);
                }
            }
        ]
    });
    //save created zone
    function changeZoneArea() {
        var l = jQuery('map').find('area#' + clickedID).length;
        if (l !== 0) {
            jQuery('#' + clickedID).attr({
                'id': 'area_' + boxName,
                'class': 'area',
                'title': boxName,
                'data-count': boxCount,
                'data-title': boxName,
                'data-dtcm': boxDtcmName,
                'data-price': boxPrice,
                'data-type': boxType
            });
            editAreaFromMapObj(clickedID, 'area_' + boxName, boxName, boxDtcmName, boxPrice, boxType, boxCount);
            click = true;
        } else {
            jQuery('.area_tmp_area').attr({
                'id': 'area_' + boxName,
                'class': 'area',
                'title': boxName,
                'data-count': boxCount,
                'data-title': boxName,
                'data-dtcm': boxDtcmName,
                'data-price': boxPrice,
                'data-type': boxType
            });
            addAreaToMapObj('area_' + boxName, boxName, boxDtcmName, boxPrice, boxType, boxCount);
        }
        createNewBox();
    }
    //when image upload give permission to add areas
    jQuery('#imageName').change(function (e) {
        if (jQuery(this).val() !== "") {
            createNewBox();
        }
    });
    //when zone is created
    jQuery('#zoneCount').change(function (e) {
        if (zoneCount <= 0) {
            jQuery('#continueEvent').addClass('hidden');
        } else {
            jQuery('#continueEvent').removeClass('hidden');
        }
        jQuery('#uploadZone > span').html(zoneCount);
    });
    //continue to event
    jQuery('#continueEvent').on('click', function (e) {
        var imageName = jQuery('#imageName').val();
        map.imgName = imageName;
        jQuery('.mainModalOverlay').hide();
        jQuery('#tmpAreaForm')[0].reset();
    });
    //if clicked on a created map
    jQuery('#drawCanvas').delegate('.area', 'click', function (e) {
        if (jQuery('#tmpAreaForm #coordsText').val() === "") {
            click = false;
            clickedID = "";
            clickedID = jQuery(this).attr('id');
            jQuery("#dialogAreaForm #boxName").val(jQuery(this).attr('data-title'));
            jQuery('#dialogAreaForm #boxDtcmName').val(jQuery(this).attr('data-dtcm'));
            jQuery("#dialogAreaForm #boxPrice").val(jQuery(this).attr('data-price'));
            jQuery("#dialogAreaForm #boxCount").val(jQuery(this).attr('data-count'));
            dialogBox.dialog("open");
            if (!click) {
                //enable delete button
                jQuery(".ui-dialog").find(".ui-button").eq(2).attr("disabled", false).removeClass("btnDisabled");
            }
        }
    });
    //draw map on canvas
    jQuery("#drawCanvas").click(function (e) {
        if (click) {
            var offset = jQuery("#drawCanvas").offset();
            if (c) {
                var x = Math.round((e.pageX - offset.left));
                var y = Math.round((e.pageY - offset.top));
                var n = x + ',' + y;
                if (jQuery("#coordsText").val() === "") {
                    jQuery("#coordsText").val(n);
                } else {
                    jQuery("#coordsText").val(jQuery("#coordsText").val() + ',' + n);
                }
                setCoordinates(x, y);
            } else {
                jQuery('.alert').fadeIn(100, function () {
                    jQuery(this).html('Starting a new area...').fadeOut(3000, function () {
                        jQuery(this).empty();
                    });
                });
                createNewBox();
            }
        }
    });
    //------------//
    //---buttons--//
    //------------//
    //clear all maps
    jQuery("#clearAll").click(function (e) {
        removeAreasFromMapObj();
        jQuery('#drawCanvas').find('map').empty();
        jQuery('#zoneMaps').empty();
        createNewBox();
        hightlight();
    });
    //clear last map
    jQuery("#clearLast").click(function (e) {
        var removeID = jQuery('#drawCanvas').find('area:last').attr('id');
        deleteAreaFromMapObj(removeID);
        jQuery('#drawCanvas').find('area:last').remove();
        createNewBox();
        hightlight();
    });
    //save zone #saveZone
    jQuery("#saveZone").click(function (e) {
        if (c && jQuery("#coordsText").val() !== "") {
            dialogBox.dialog("open");
            //disable delete button
            jQuery(".ui-dialog").find(".ui-button").eq(2).attr("disabled", true).addClass("btnDisabled");
        } else {
            jQuery('.alert').fadeIn(100, function () {
                jQuery(this).html('Cannot save, there is no box have created').fadeOut(6000, function () {
                    jQuery(this).empty();
                });
            });
        }
        seats = [];
    });
    //enable Draggable Resizable
    function enableDraggableResizable() {
        jQuery('#seatForm #drawCanvas > div').each(function (e) {
            jQuery(this).draggable().resizable();
        });
        var obj = map.areas;
        jQuery.each(obj, function (key, value) {
            if (value.zoneID === uploadMap) {
                value.zoneSeats = [];
            }
        });
        enableOptions();
    }
    //------------//
    //---buttons--//
    //------------//
    //
    jQuery('#zoneMaps').delegate('#uploadMap', 'click', function (e) {
        uploadMap = jQuery(this).data('id');
        boxCount = jQuery(this).data('count');
        uploadStatus = jQuery('#zoneMaps').find('#map_' + uploadMap).attr('data-upload');
        if (uploadStatus === 'true') {
            var obj = map.areas;
            var mapImg;
            jQuery.each(obj, function (key, value) {
                if (value.zoneID === uploadMap) {
                    var seatObj = value.zoneSeats.seatPlan;
                    mapImg = value.zoneSeats.mapImg;
                    jQuery.each(seatObj, function (k, v) {
                        var t = v.options.top;
                        var l = v.options.left;
                        var w = v.options.width;
                        var h = v.options.height;
                        jQuery('#seatForm #drawCanvas').append('<div id="' + v.seatID + '" class="' + v.type + '" style="position: absolute; cursor: move; left: ' + l + '; top: ' + t + '; width:' + w + '; height:' + h + ';"><label class="whiteLable">' + v.seatID.split('_')[1] + '</label></div>');
                    });
                }
            });
            jQuery('#seatFloor').attr('src', FULL_URL_PATH + '/uploads/maps/' + mapImg);
            jQuery('#mapName').val(mapImg).trigger('change');
            jQuery('#seatForm #drawCanvas').fadeIn('slow');
            jQuery('#continueZone').removeClass('hidden');
            enableDraggableResizable();
        } else {
            jQuery('#zoneMaps').find('#map_' + uploadMap).attr('data-upload', 'true');
        }
        jQuery('.modalOverlay').show();//before show load the data
    });
    //
    jQuery('#zoneMaps').delegate('#deleteMap', 'click', function (e) {
        var deleteMap = jQuery(this).data('id');
        deleteAreaFromMapObj(deleteMap);
    });
    //prevent typing text
    jQuery("#dialogAreaForm #boxPrice").keypress(function (e) {
        //if the letter is not digit then display error and don't type anything
        if (e.which !== 8 && e.which !== 0 && (e.which < 48 || e.which > 57)) {
            //display error message
            return false;
        }
    });
    //prevent typing text
    jQuery("#dialogAreaForm #boxCount").keypress(function (e) {
        //if the letter is not digit then display error and don't type anything
        if (e.which !== 8 && e.which !== 0 && (e.which < 48 || e.which > 57)) {
            //display error message
            return false;
        }
    });
    //modalOverlay jQuery
    function addNewShape() {
        var position = jQuery('#seatForm #drawCanvas').position();
        var l = Math.round(position.left);
        var t = Math.round(position.top);
        jQuery('#newShape.square, #newShape.rectangle, #newShape.circle, #newShape.oval').draggable().css({'position': 'absolute', 'cursor': 'move', 'left': l, 'top': t});
        jQuery('#newShape.rectangle').resizable({
            //animate: true,
            //ghost: true,
            //aspectRatio: 16 / 9,
            minHeight: 20,
            minWidth: 5
        });
        jQuery('#newShape.square').resizable({
            //animate: true,
            //ghost: true,
            aspectRatio: 1 / 1,
            minHeight: 30,
            minWidth: 30
        });
        jQuery('#newShape.circle').resizable({
            //animate: true,
            //ghost: true,
            aspectRatio: 1 / 1,
            minHeight: 30,
            minWidth: 30
        });
        jQuery('#newShape.oval').resizable({
            //animate: true,
            //ghost: true,
            aspectRatio: 16 / 9,
            minHeight: 20,
            minWidth: 5
        });
        var elmId = 'sample_' + (Math.floor(Math.random() * 30) + 1);//random ID
        jQuery('#newShape.square, #newShape.rectangle, #newShape.circle, #newShape.oval').attr('id', elmId);
    }
    //adding to seat selection array
    function addToSeatSelectionArray(id, n) {
        if (n === 0) {
            selections = [];
            selections.push(id);
        } else {
            if (jQuery.inArray(id, selections) > -1) {
                console.log("is in array");
            } else {
                selections.push(id);
            }
        }
    }
    function deleteFromSeatSelectionArray(id) {
        if (id === 'deleteSelected') {
            var le = selections.length;
            for (var i = 0; i <= le; i++) {
                id = selections[i];
                jQuery('#drawCanvas #' + id).remove();
            }
        } else {
            jQuery('#drawCanvas #' + id).remove();
        }
        selections = [];
    }
    //check Created Seats
    function checkCreatedSeats() {
        //length = jQuery('#drawCanvas div[id^="seatID_"]').length;
        length = jQuery('#drawCanvas .ui-draggable').length;
        if (length < boxCount) {
            jQuery('#mapCount').val((parseInt(boxCount, 10) - parseInt(length, 10))).trigger('change');
            return true;
        } else {
            jQuery('#mapCount').val((parseInt(boxCount, 10) - parseInt(length, 10))).trigger('change');
            return false;
        }
    }
    //delete button creating
    function createDeleteBtn(id, n) {
        jQuery('#deleteDiv').empty();
        if (n > 0) {
            jQuery('#deleteDiv').append('<button id="deleteShape" data-id="deleteSelected" class="btn btn-primary btn-sm">Delete Selected Seats<spa class="glyphicon glyphicon-remove-sign"></spa></button>');
        } else {
            jQuery('#deleteDiv').append('<button id="deleteShape" data-id="' + id + '" class="btn btn-primary btn-sm">' + id.split('_')[1] + '<spa class="glyphicon glyphicon-remove-sign"></spa></button>');
        }
    }
    //enable options
    function enableOptions() {
        //when change white label
        jQuery('.ui-draggable').delegate('input.whiteLable', 'change', function (e) {
            e.stopImmediatePropagation();
            var val = jQuery(this).val();
            jQuery(this).parent('div').attr('id', 'seatID_' + val);
            jQuery(this).remove();
            jQuery('#seatID_' + val).append('<label class="whiteLable">' + val + '</label>');
        });
        //when click on a created seats
        jQuery('.ui-draggable').on('click', function (e) {
            var Id = "";
            e.stopImmediatePropagation();
            Id = jQuery(this).attr('id');
            if (Id.indexOf("sample_") >= 0) {
                createDeleteBtn(Id, 0);
            } else {
                if (e.ctrlKey) {
                    createDeleteBtn(Id, 1);//create 
                    addToSeatSelectionArray(Id, 1);//add to array multi selection
                } else {
                    createDeleteBtn(Id, 0);//create 
                    addToSeatSelectionArray(Id, 0);//add to array multi selection
                }
            }
        });
        //delete button click
        jQuery('#deleteDiv').delegate('#deleteShape', 'click', function (e) {
            e.stopImmediatePropagation();
            var attr = jQuery(this).attr('data-id');
            if (attr === 'deleteAll') {
                deleteFromSeatSelectionArray(attr);
            } else {
                deleteFromSeatSelectionArray(attr);
                jQuery('#drawCanvas #' + attr).remove();
            }
            jQuery(this).remove();
            checkCreatedSeats();
        });
    }
    //adding a custom shape
    function addCustomShape(type) {
        var c = checkCreatedSeats();//check Created Seats
        if (c) {
            var elmId = 'sample_' + (Math.floor(Math.random() * 30) + 1);
            jQuery('#seatForm #drawCanvas').append('<div id="newShape" class="' + type + '"><input id="' + elmId + '" name="' + elmId + '" class="whiteLable" type="text" value="SeatID"/></div>');
            addNewShape();//new shape added
            enableOptions();//active other functions
            checkCreatedSeats();//check Created Seats
        } else {
            alert('No seats available for create...');
        }
    }
    //when click on shapes
    jQuery('.shapes li').on('click', function (e) {
        if (active) {
            addCustomShape(jQuery(this).attr('id'));
        } else {
            alert('Please upload an image first');
        }
    });
    //when image upload give permission to add areas
    jQuery('#mapName').change(function (e) {
        if (jQuery(this).val() !== "") {
            active = true;
            jQuery('#zoneID').val(uploadMap);
            jQuery('#mapCount').val(boxCount).trigger('change');
        }
    });
    //mapCount on change
    jQuery('#mapCount').change(function (e) {
        var l = jQuery('#seatForm #drawCanvas > div').length;
        if (l === 0) {
            jQuery(this).val(boxCount);
        }
        if (parseInt(jQuery(this).val()) > 0) {//boxCount
            jQuery('#continueZone').removeClass('hidden');
            if (parseInt(jQuery(this).val()) === boxCount) {
                jQuery('#continueZone').addClass('hidden');
            }
        }
        jQuery('#mapZone > span').empty().html(jQuery(this).val());
    });
    //clear created seats
    jQuery('#clearZone').on('click', function (e) {
        copyArray = [];
        jQuery('#seatForm #drawCanvas > div').each(function (e) {
            jQuery(this).remove();
        });
        jQuery('#draw-shapes #deleteDiv').empty();
        jQuery('#mapCount').trigger('change');
    });
    //created seats save to zone
    jQuery('#continueZone').on('click', function (e) {
        var mapObj = new Object();
        var zoneID = jQuery('#zoneID').val();
        var mapName = jQuery('#mapName').val();
        var mapCount = jQuery('#mapCount').val();
        var i = 0;
        seats = [];
        jQuery('#seatForm #drawCanvas > div').each(function (index, value) {
            var seatObj = new Object();
            seatObj.seatID = value.id;
            seatObj.type = jQuery('#seatForm #drawCanvas #' + value.id).attr('class').split(' ')[0];
            seatObj.options = {
                'top': jQuery('#seatForm #drawCanvas #' + value.id).css('top'),
                'left': jQuery('#seatForm #drawCanvas #' + value.id).css('left'),
                'width': jQuery('#seatForm #drawCanvas #' + value.id).css('width'),
                'height': jQuery('#seatForm #drawCanvas #' + value.id).css('height')
            };
            seats.push(seatObj);
            i++;
        });
        mapObj.mapImg = mapName;
        mapObj.mapCount = mapCount;
        mapObj.seatPlan = seats;
        var obj = map.areas;
        jQuery.each(obj, function (key, value) {
            if (value.zoneID === zoneID) {
                value.zoneSeats = [];
                value.zoneSeats = mapObj;
            }
        });
        jQuery('#clearZone').trigger('click');
        jQuery('#tmpZoneForm')[0].reset();//reset forms
        jQuery('#drawCanvas img#seatFloor').attr('src', '#');
        jQuery('.modalOverlay').hide();
    });
    //get next char letter
    function nextChar(c) {
        return String.fromCharCode(c.charCodeAt(0) + 1);
    }
    //increase Numbering and Lettering
    function increaseNumbering(type) {
        var tmpID;
        jQuery.each(copyArray, function (i, v) {
            var t = parseInt(jQuery('#seatForm #drawCanvas #' + v.id).css('top'));
            var l = parseInt(jQuery('#seatForm #drawCanvas #' + v.id).css('left'));
            var w = parseInt(jQuery('#seatForm #drawCanvas #' + v.id).css('width'));
            var h = parseInt(jQuery('#seatForm #drawCanvas #' + v.id).css('height'));
            if (type === 'number') {
                l = (l) + (w) + 10; // to left only 09122015
                var txt = v.id.replace(/\d+/g, '');
                var num = v.id.replace(/^\D+/g, '');
                tmpID = txt + parseInt(++num);
            } else if (type === 'letter') {
                t = (t) + (h) + 10; // to top only 09122015
                var txt = v.id.replace(/\d+/g, '');
                var char = txt.split('_');
                var num = v.id.replace(/^\D+/g, '');
                tmpID = nextChar(char[1]);
                tmpID = char[0] + '_' + tmpID + num;
            }
            var cls = jQuery('#seatForm #drawCanvas #' + v.id).attr('class').split(' ')[0];
            if (jQuery('#seatForm #drawCanvas #' + tmpID).length === 0) {//check if element is exist 
                jQuery('#seatForm #drawCanvas #' + v.id).clone().prop({'id': tmpID, 'class': cls}).css({'left': l, 'top': t}).appendTo("#seatForm #drawCanvas").empty();
                jQuery('#seatForm #drawCanvas #' + tmpID).append('<label class="whiteLable">' + tmpID.split('_')[1] + '</label>').draggable().resizable();
            }
        });
        copyArray = []; //after paste make it empty
        enableOptions();
        checkCreatedSeats();
    }
    //KEYBOARD BUTTON MAPPING
    jQuery(document).keyup(function (e) {
        if (e.keyCode === 90 && e.ctrlKey) {
            undoLastCoordinates(newName);
        }
        //copy seats
        if (e.keyCode === 67 && e.ctrlKey) {
            copyArray = [];
            jQuery.each(selections, function (i, v) {
                var copyObj = new Object();
                copyObj.id = v;
                copyArray.push(copyObj);
            });
        }
        //paste seats
        if (e.keyCode === 86 && e.ctrlKey) {
            if (copyArray.length > 0) {
                jQuery('body').append('<div id="txtOrnum" title="Letter Or Number"><p>Increase Letter Or Number?</p></div>');
                $("#txtOrnum").dialog({
                    dialogClass: "noClose",
                    //autoOpen: false,
                    closeOnEscape: false,
                    draggable: false,
                    title: "Letter Or Number",
                    resizable: false,
                    modal: true,
                    buttons: {
                        "Letter": function () {
                            jQuery(this).dialog("close");
                            jQuery(this).remove();
                            increaseNumbering('letter');
                        },
                        "Number": function () {
                            jQuery(this).dialog("close");
                            jQuery(this).remove();
                            increaseNumbering('number');
                        }
                    }
                });
            }
        }
    });
    //
    jQuery('#eventMap').click(function (e) {
        e.preventDefault();
        click = true;
        jQuery('.mainModalOverlay').show();
    });
    //
    jQuery('.saveMapBtn').click(function (e) {
        var attr = jQuery('.uploadEventMap button').attr('id');
        if (attr === 'editMap') {
            click = true;
        }
        if (click) {
            jQuery('.uploadEventMap span').html('Event created successfully');
            var eventID, layoutID, path;
            eventID = jQuery('#event_id').val();
            layoutID = jQuery('#layout_id').val();
            path = FULL_URL_PATH + '/admin/event/ajaxsavedrawing';
            var id = jQuery(this).attr('id');
            if (id === 'publish') {
                var jsonData = JSON.stringify(map);
                jQuery.ajax({
                    type: 'POST',
                    url: path,
                    async: false,
                    data: {'jsonData': jsonData, 'eventID': eventID, 'layoutID': layoutID},
                    success: function (response) {
                        if (response === 'success') {
                            map = {'imgName': '', 'areas': []};//map object
                            areas = {};//area object
                            seats = [];//seats object
                            window.location.replace('http://' + window.location.hostname + FULL_URL_PATH + '/admin/event/list');
                        }
                    }
                });
            }
        } else {
            alert('Please upload a map');
        }
    });
    //
    jQuery('#editMap').click(function (e) {
        e.preventDefault();
        var eventID, layoutID, path;
        eventID = jQuery('#event_id').val();
        layoutID = jQuery('#layout_id').val();
        path = FULL_URL_PATH + '/admin/event/ajaxeditdrawing';
        jQuery.ajax({
            type: 'POST',
            url: path,
            dataType: 'json',
            async: false,
            data: {'eventID': eventID, 'layoutID': layoutID},
            success: function (response) {
                if (response.status === 'success') {
                    map = response.mapObject;
                    var areas = map.areas;
                    jQuery.each(areas, function (i, v) {
                        jQuery('#drawCanvas map#Map').append('<area shape="' + v.zoneType + '" id="' + v.zoneID + '" class="area" coords="' + v.zoneMaps + '" title="' + v.zoneTitle + '" data-count="' + v.zoneCount + '" data-title="' + v.zoneTitle + '" data-price="' + v.zonePrice + '" data-type="' + v.zoneType + '">');
                        jQuery('#zoneMaps').append('<div id="map_' + v.zoneID + '" class="btn-group btn-block" data-upload="true">\n\
                            <label>Upload ' + v.zoneTitle + ' seats</label>\n\
                            <button id="uploadMap" class="btn btn-primary" data-id="' + v.zoneID + '" data-count="' + v.zoneCount + '" data-toggle="modal" data-target="#seatAreaModal">\n\
                                <span class="glyphicon glyphicon-upload"></span> Upload\n\
                            </button>\n\
                            <button id="deleteMap" class="btn btn-primary" data-id="' + v.zoneID + '">\n\
                                <span class="glyphicon glyphicon-remove"></span>\n\
                            </button></div>');
                    });
                    jQuery('#mainFloor').attr('src', FULL_URL_PATH + '/uploads/maps/' + map.imgName);
                    jQuery('#mainForm #output').html('Editing image added').delay(3000).fadeOut(function () {
                        jQuery('#imageName').val(map.imgName).trigger("change");
                        jQuery('#drawCanvas').fadeIn('slow');
                    });
                    hightlight();//make hightlight
                    jQuery('.mainModalOverlay').show();
                }
            }
        });
    });
});