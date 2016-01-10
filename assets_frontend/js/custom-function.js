// JavaScript Document



var winScroll;



$(document).ready(function() {

	

    $('.modal').on('hide.bs.modal', function(e) {

        $("label.error").hide();

            if(!$('body').hasClass('modal-closed'))

            {

                    winScroll = $('body').css('padding-right');

            }

            $('body').addClass('modal-closed');

    });

    $('.modal').on('hidden.bs.modal', function(e) {		

            $('body').removeAttr('style');

    });

    $('.modal').on('shown.bs.modal', function(e) {

            if($('body').hasClass('modal-closed'))

            {

                    $('body').removeAttr('style');

                    $('body').css('padding-right', winScroll);

                    $('body').addClass('modal-open');

            }

    });

		

    /*

    $(document).on('click', '[data-toggle=modal]', function(e){

            var newModal = $(this).data('target');

            if($('.modal').is(':visible')){

                    $(newModal).on('show.bs.modal', function(e) {

                            e.stopPropegation();

                    });

                    alert ('A modal is opened already.');

                    return false;

            }

    })



    /*

    $('.open-signup-modal').click(function(){                

            $('#login').modal('hide');

            $('#forgotpassword').modal('hide');

            $("label.error").hide();

            document.getElementById("userlogin").reset();

            document.getElementById("forgotpass").reset();                

            /*

             $('#login').one('hidden.bs.modal', function (e) {

                $('#signup').modal('show');

            })

             *\/

            $('#signup').modal('show');                

    });



    $('.open-login-modal').click(function(){                

            $('#forgotpassword').modal('hide');                

            document.getElementById("usersignup").reset();

            document.getElementById("forgotpass").reset();                

            $("label.error").hide();

            $('#signup').modal('hide');

            $('#login').modal('show');

            /*$('#forgotpassword').click('hidden.bs.modal', function (e) {

                $('#login').modal('show');

            })*\/

    });



    $('.open-forgot-modal').click(function(){                

            document.getElementById("userlogin").reset();

            document.getElementById("usersignup").reset();

            $("label.error").hide();

            $('#login').modal('hide');

            $('#signup').modal('hide');                

            $('#forgotpassword').modal('show');

            /*$('#login').one('hidden.bs.modal', function (e) {

                $('#forgotpassword').modal('show');

            })*\/		

    });    



    */    



    $(".close").click(function(e){

        document.getElementById("usersignup").reset();

        document.getElementById("userlogin").reset(); 

        document.getElementById("forgotpass").reset();

    })

	/*

	if($( ".select2" ).length)

$( ".select2" ).select2( { placeholder: "Dubai", maximumSelectionSize: 6 } );

	

	if($( ".select3" ).length)

$( ".select3" ).select2( { placeholder: "United Arab Emirates", maximumSelectionSize: 6 } );



	

	if($( ".select4" ).length)

$( ".select4" ).select2( { placeholder: "City", maximumSelectionSize: 6 } );

	

	if($( ".select5" ).length)

$( ".select5" ).select2( { placeholder: "Country", maximumSelectionSize: 6 } );

	*/

	/* if($( ".month" ).length)

$( ".month" ).select2( { placeholder: "Month", maximumSelectionSize: 6 } );

	

	if($( ".year" ).length)

$( ".year" ).select2( { placeholder: "Year", maximumSelectionSize: 6 } );*/



});





$( ".show-more" ).click(function() {

    $( ".show-more-content" ).slideToggle( "slow", function(){

		if($(this).is(':visible')){

			$( ".show-more" ).html('<i class="icon-less"></i><br>show Less');
            $( "#eventDetails" ).show();

		} else {

			$( ".show-more" ).html('show more<br><i class="icon-more"></i>');
            $( "#eventDetails" ).hide();

		}

	 }); 

});

$( "#selectDateDiv" ).click(function() {
    $( ".show-more-content" ).slideToggle( "slow", function(){

        if($(this).is(':visible')){

            $( ".show-more" ).html('<i class="icon-less"></i><br>show Less');
            $( "#eventDetails" ).show();

        } else {

            $( ".show-more" ).html('show more<br><i class="icon-more"></i>');   
            $( "#eventDetails" ).hide();

        }

    }); 
});

  

$("[data-update]").mouseenter(function(){

    var element = $(this).data('update');

    $(element).addClass('active');

	//console.log(element);

	var res = $(element).attr('class').split(" ");        

    var color =$(".event-contents ul li."+res[0]+" .icon-stage").css('background-color');

    $(this).css('border','2px solid '+color);		

  }).mouseleave(function(){

    var element = $(this).data('update');	

    $(element).removeClass('active');

	

	var res = $(element).attr('class').split(" ");         

	var chk = "#mapHolder [data-update='."+res[0]+"']";

	$(chk).css('border','none');  		

});





$( "body" ).delegate( ".myticket", "mouseenter", function() {

    var res = $(this).attr('class').split(" ");        

    var color =$(".event-contents ul li."+res[0]+" .icon-stage").css('background-color');

    var chk = "#mapHolder [data-update='."+res[0]+"']";

    $(chk).css('border','2px solid '+color);

	

	$(".event-contents ul li."+res[0]).addClass('active');

});

$( "body" ).delegate( ".myticket", "mouseleave", function() {

    var res = $(this).attr('class').split(" ");         

    var chk = "#mapHolder [data-update='."+res[0]+"']";

    $(chk).css('border','none');  

	

	$(".event-contents ul li."+res[0]).removeClass('active');

});

  

$('[data-toggle="tooltip"]').tooltip(); 



