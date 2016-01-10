/**
 * This function check html tags in field value
 */
function checkHTMLTags(value, element, params) { 
    if(value.match(/([\<])([^\>]{1,})*([\>])/i)==null){
        return true;
    }else{
        return false;
    }
}

/**
 * checkUniqueEmail
 * @param {string} value
 * @param {object} element
 * @param {mixed} params
 * @returns {Boolean}
 */
function checkUniqueEmail(value, element, params) {
    $.ajax({
        cache:false,
        async:false,
        type: "GET",
        url: FULL_URL_PATH + 'checkemail/'+value,
        success: function(data)
        {
            if(data==1)
            {
                result = false;						
            }else if(data == 2){
                result = false; /* Email id not received on server */
            }
            else
            {
                result = true;
            }
	}
    });
  
    return result;
}

$(document).ready(function(){
   /* On click of Sign up button */ 
   $('#btnSignUpPop').click(function(){
       $("#usersignup input[type=text]").val('');
       $('label.error').hide();
       $('#signupsucssmsg').hide();
   });
});
