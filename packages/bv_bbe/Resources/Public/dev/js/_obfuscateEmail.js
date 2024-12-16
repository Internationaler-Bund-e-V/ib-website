/**
 * initialzie mailcrypt 
 */
function initializeMailcrypt() {
  var ibMail;
  var ibReferrer = "Freitext";
  var ibLocationID;
  $('.ibOEmail').on('click', function () {
    ibMail = $(this).data('ibemail');
    ibReferrer = $(this).data('ibemailreferrer');
    ibLocationID = 0;

    if (ibReferrer == undefined) {
      ibReferrer = "Freitext";
    }
    if (ibReferrer == 'Standort_mail') {
      ibLocationID = $(this).data('locationid');
    }


    var emDialog = $('#eMailDialog');
    emDialog.show();
    $('#emdCloseButton').on('click', function () {
      emDialog.hide();
      $('#showEmailAddress').empty();
    })
  })

  $('#btnShwoMail').click(function () {
    $('#showEmailAddress').html(UnCryptMailto(ibMail));
  });

  $('#btnOpenMailClient').click(function () {
    $('#btnOpenMailClient').attr('href', "mailto:" + UnCryptMailto(ibMail));
  });

}

function UnCryptMailto(s) {
  var o = s.split('#i3B1*')[1];
  var s = s.split('#i3B1*')[0];
  var n = 0;
  var r = "";

  for (var i = 0; i < s.length; i++) {
    n = s.charCodeAt(i);
    var code = n - o;

    if (code < 0) {
      code = code + 127
    }

    r += String.fromCharCode(code);

  }
  return r;
}

export function initObfuscateEmail() {
  initializeMailcrypt();
}





