$(document).ready(function() {
    !verboseBuild || console.log('-- starting proton.intro build');
    
    proton.intro.build();
});

proton.intro = {
	build: function () {
		setTimeout(function() {
			introJs().setOptions({'showStepNumbers': false}).start();
		}, 1300);
	}	
}