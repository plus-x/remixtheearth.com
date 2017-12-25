BN = {} || BN;

jQuery(document).ready(function() {
  jQuery('button.bn-refresh').click(function(e) {
    e.preventDefault();
    window.sessionStorage.removeItem('beacon');
    window.location.reload();
    return false;
  });
});

BN.showIssues = function() { };
BN.loadIssues = function() {

  if (typeof BN_target === 'undefined' || BN_target === false) {
    return;
  }

  BN.data = window.sessionStorage.getItem('beacon');

  var $ = jQuery,
      html = '',
      i;

  var postProcess = function() {
    var now = parseInt( Date.now(), 10 );
    if (BN.data.expires < now) {
        window.sessionStorage.removeItem('beacon');
        BN.loadIssues();
        return;
    }
    BN.title = BN.data.publication.title;
    BN.url = BN.data.publication.url;
    BN.issues = BN.data.issues;
    BN.showIssues();
    if ($('.beacon-connect-info').length > 0) {

      for (i = 0; i < BN.issues.length; i += 1) {
        html += '<li>'+BN.issues[i].title+'</li>';
      }
      $('.bn-title').text(BN.title);
      $('.bn-url').text(BN.url);
      $('.bn-issues').html(html);

    }
  };

  if (BN.data) {
    BN.data = JSON.parse(BN.data);
    postProcess();
  } else {
    $('.beacon-by-admin-wrap').addClass('loading');

    $.ajax({
      type: 'GET',
        url: BN_target,
        success: function(json) {
          var now = parseInt( Date.now(), 10 ),
              later = now;
              later = now + (60 * 10 * 1000);
          json.expires = later;

          window.sessionStorage.setItem('beacon', JSON.stringify(json));
          BN.data = json;
          postProcess();
          $('.beacon-by-admin-wrap').removeClass('loading');
        },
        error: function(e) {
          $('.beacon-by-admin-wrap').removeClass('loading');
          // console.log(e);
          // alert('Oops! Please try again. There has been an error: ' + e.message );
          // console.log(e.message);
        }
    });

  }

};



window.addEventListener('load', function() {

  BN.loadIssues();

}, false);


// BN.receiveMessage = function(e) {
// 
//   var $ = jQuery;
// 
// 	if ( e.origin.indexOf('//:beacon.') ) {
// 		var data = $.parseJSON( e.data ),
// 			html = [];
// 
// 		BN.logged_on = (typeof data.publication !== 'undefined');
// 		BN.title = data.publication;	
// 		BN.url = e.origin + '/' + data.url;
// 		BN.issues = data.issues;
// 		BN.showIssues();
// 
// 		html.push('<hr />');
// 		html.push('<p>');
// 
// 		if ( BN.logged_on ) {
// 			html.push('<i class="fa fa-check"></i>');
// 			html.push('Beacon logged in as: ');
// 			html.push(BN.title);
// 		} else {
// 			$('.prompt-login').show();
// 			$('.requires-login').css('opacity', 0.6);
// 			$('.requires-login button').attr('disabled', 'disabled');
// 			$('.requires-login a').attr('disabled', 'disabled');
// 			html.push('<p>');
// 			html.push('<i class="fa fa-warning"></i>');
// 			html.push('Not logged into Beacon - ');
// 			html.push('<a href="'+ e.origin +'" target="_blank">');
// 			html.push('Log in');
// 			html.push('</a>');
// 		}
// 
// 		html.push('</p>');
// 
// 		$('.beacon-account').html(html.join(''));
// 
// 
// 		if ( $('.beacon-status') ) {
// 			$('.beacon-status').html(html);	
// 			if (BN.logged_on) {
// 				$('.beacon-status').addClass('success');
// 			} else {
// 				$('.beacon-status').addClass('fail');
// 			}
// 		}
// 	}
// };



// window.addEventListener('message', function(e) {
// 	BN.receiveMessage(e);
// }, false );
