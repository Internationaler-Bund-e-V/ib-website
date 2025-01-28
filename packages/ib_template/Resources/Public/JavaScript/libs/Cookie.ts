/**
 * check for get parameter ibAnchor for scrolling and opening contentelements
 * usage: ?ibAnchor = cXXX or XXX
 * c -> content element id
 * XXX -> accordion element id
 */

export function setCookie(cname:string, cvalue:string, exdays:number) {
    const d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    
    document.cookie = cname + '=' + cvalue + ';expires=' + d.toUTCString() + ';path=/';
}

export function getCookie(cname:string):string {
    const name:string = cname + "=";
    const nameLength:number = name.length;

    let ca:Array<string> = document.cookie.split(';');
    const caLength:number = ca.length;

    
    for (let i:number = 0; i < caLength; i++) {
        let c:string = ca[i];
        
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return '';
}

