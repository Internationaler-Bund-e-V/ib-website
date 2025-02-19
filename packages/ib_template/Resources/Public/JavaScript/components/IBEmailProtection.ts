import $ from 'jquery';

class IBEmailProtection
{
    protected ibMail:string = '';
    protected ibReferrer:string = "Freitext";
    protected ibLocationID:number = 0;

    constructor(elements:any) {

        $(elements).on('click', (event) => {
            this.ibMail = $(event.currentTarget).data('ibemail');
            this.ibReferrer = $(event.currentTarget).data('ibemailreferrer');
            this.ibLocationID = 0;

            if (this.ibReferrer == undefined) {
                this.ibReferrer = "Freitext";
            }
            if (this.ibReferrer == 'Standort_mail') {
                this.ibLocationID = $(event.currentTarget).data('locationid');
            }


            var emDialog = $('#eMailDialog');
            emDialog.show();
            $('#emdCloseButton').on('click', function () {
                emDialog.hide();
                $('#showEmailAddress').empty();
            })
        })

        $('#btnShwoMail').on('click', () => {
            $('#showEmailAddress').html(this.UnCryptMailto(this.ibMail));
            this.trackContact(this.ibMail, this.ibReferrer, this.ibLocationID);
        });

        $('#btnOpenMailClient').on('click', () => {
            $('#btnOpenMailClient').attr('href', "mailto:" + this.UnCryptMailto(this.ibMail));
            this.trackContact(this.ibMail, this.ibReferrer, this.ibLocationID);
        });

    }
    UnCryptMailto(s:string) {
        let o:any = s.split('#i3B1*')[1];
        s = s.split('#i3B1*')[0];
        let n:any = 0;
        let r:string = '';

        for (let i = 0; i < s.length; i++) {
            n = s.charCodeAt(i);
            let code:any = n - o;

            if (code < 0) {
                code = code + 127
            }

            r += String.fromCharCode(code);

        }
        return r;
    }

    trackContact(ibMail:string, ibReferrer:string, ibLocationID:number) {
        if (typeof (window as any)._paq == "undefined") {
            return;
        }
        if (ibLocationID != 0) {
            (window as any)._paq.push(['trackEvent', 'Kontakt', ibReferrer, ibLocationID]);
        }
        else {
            (window as any)._paq.push(['trackEvent', 'Kontakt', ibReferrer, this.UnCryptMailto(ibMail)]);
        }
    }

}

export default IBEmailProtection;
