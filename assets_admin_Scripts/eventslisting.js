var oTable, oSettingsusers, oHeader;
var listUserType ='S';  
$(document).ready(function() {                
    oSettingsusers = $('#datatableevents').dataTable( {
        "bDestroy": true,
        "iDisplayLength": 10,
        "aLengthMenu": [[10, 25, 50, 10000], [10, 25, 50, "All"]],
        "bPaginate": true,
        "bStateSave": false,
        "pagingType": "full_numbers",
        "sDom": '<lfr>t<"F"ip>',
        "aoColumns": [{ },{ },{ },{ },{ "bSortable": false }],
        "bProcessing": true,
        "bServerSide": true,
        "bLengthChange": true,                                        
        //"sPaginationType": "bootstrap",
        "bSortable": true,
        "oLanguage": { "sEmptyTable": "No matching results found." },
        "fnRowCallback": function( nRow ){
                $('#datatableevents_filter label input').attr("placeholder", "Search from here");										
        },						        		
    "sAjaxSource": FULL_URL_PATH+"/admin/event/ajaxeventslist"
    });	
});