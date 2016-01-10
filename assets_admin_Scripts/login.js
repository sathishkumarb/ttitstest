$( document ).ready(function() {
		
	$("#adminlogin").validate({
		errorClass:'validation-error',
		rules: {				
			email: { 
                            required: true,
                            email: true
                        },
			password:{ required: true },
		},
                messages: {           
			'email' : {
                            required: "Enter Admin Username",
                            email: "Enter valid email"				
                         },
                        password: { 
                            required: "Enter Admin Password"				
                        }            
                },
		submitHandler: function(form) {
			form.submit();
			return true;
		}
	});
});