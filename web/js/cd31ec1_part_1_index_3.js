;$(document).ready(function(){for(var o=1;o<=3;o++){setTimeout(addShadow,o*1000,o);setTimeout(removeShadow,o*1000+1000,o)}});function addShadow(o){$('.small-link:nth-of-type('+o+')').addClass('shadowed')};function removeShadow(o){$('.small-link:nth-of-type('+o+')').removeClass('shadowed')};