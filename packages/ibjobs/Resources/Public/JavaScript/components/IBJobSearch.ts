import $ from 'jquery';

class IBJobSearch
{
    protected jobs:any;
    protected jobContainer: HTMLElement | JQuery<HTMLElement> | null;
    protected results:Array<any> = [];
    protected jobTemplate:string = '';
    protected jobtype:string = "IB";
    protected srJobTemplate:string = '';
    protected trfTemplate:string = "";
    protected lazyOffset:number = 0;
    protected currentJob:number = 0;
    protected currentResults:number = 0;
    protected alternateClass:string = 'odd';
    protected resultCounter: HTMLElement | JQuery<HTMLElement> | null;
    protected stickyWidth:number = 0;
    protected searchExtended:boolean = false;
    protected extendedFilterActive:boolean = false;
    protected targetPage:string = '';


    constructor(element:Element|HTMLElement|JQuery<HTMLElement>) {
        this.jobContainer = $('#ib-jobs-lazy-container')!;
        this.resultCounter = $('#ib-jobs-results-count')!;
        const dataset = (element as HTMLElement).dataset;
        this.targetPage = dataset.target!;
        let clients = dataset.clients;
        let srclients = dataset.srclients;
        let intern = dataset.intern;
        let locations = dataset.locations;
        let categories = dataset.categories;
        let titles = dataset.titles;
        let prefilter = dataset.prefilter;
        let rmBaseUrl = dataset.baseurl;
        let baseUrl = '/proxy/ibjobs.php';
        let requestURL = "";

        if (prefilter == '1') {
            requestURL = baseUrl + "?clients=" + clients + "&sr_clients=" + srclients + "&intern=" + intern + "&locations=" + locations + "&categories=" + categories + "&titles=" + titles + "&baseurl=" + rmBaseUrl;
        } else {
            requestURL = baseUrl + "?clients=" + clients + "&sr_clients=" + srclients + "&intern=" + intern + "&baseurl=" + rmBaseUrl;
        }

        console.log(requestURL);
        $.ajax({
            url: requestURL,
            context: document.body
        }).done((data) => {
            $('.ib-jobs-loader, .ib-jobs-container').toggle();
            this.setStickyWidth();
            this.jobs = JSON.parse(data)!.data;
            this.results = this.jobs;
            this.initJobs();
            this.setEvents();

            window.addEventListener('scroll', () => {
                if ($('.ib-jobs-search-container').is(":visible")) {
                    this.checkFixed();
                }
            });
        });
    }

    searchTerms(terms:Array<any>|string, strict:boolean = false) {
        this.results = [];


        /*
        ** strict search for select options
        */

        if (strict) {
            var found = false;
            $.each(this.jobs, (key, val) => {
                if (
                    (terms[0].text == "true" || val[terms[0].val] == terms[0].text) &&
                    (terms[1].text == "true" || val[terms[1].val] == terms[1].text) &&
                    (terms[2].text == "true" || val[terms[2].val] == terms[2].text)
                ) {
                    this.results.push(this.jobs[key]);
                }
            });
        }

        /*
        *  normal search
        */
        else {
            let multipleTerms: RegExpMatchArray = (terms as string).match(/\S+/g)!;
            var tmpRegex = "";
            var limitTerms = 0;
            if (multipleTerms && multipleTerms.length) {
                limitTerms = multipleTerms.length;
                for (var i = 0; i < limitTerms; i++) {
                    multipleTerms[i] = multipleTerms[i].replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
                    tmpRegex += "(?=.*" + multipleTerms[i] + ")";
                }
                this.jobs.forEach((val: Array<string>, key: string) => {
                    let regex = new RegExp(tmpRegex, "i");
                    if (val[15].search(regex) != -1) {
                        this.results.push(this.jobs[key]);
                    }
                });
            } else {
                this.results = this.jobs;
            }




        }

        this.initJobs();
    }

    addJobRow(job:Array<string>) {
        /*
        *   attrib list
        */
        // 0 : description
        // 1 : city
        // 2 : category
        // 3 : chiffre
        // 4 : federal state
        // 5 : public since
        // 6 : region/client
        // 7 : job id
        // 8 : trf_key
        // 9 : trf_description
        // 10: intern
        // 11: name
        // 12: a_id
        // 13: jobtype
        // 14 : search values
        // 15 : External jobdetail URL

        let trfTemplate = "";
        if (job[10] == '1' && job[9] != "") {
            trfTemplate = '<span class="ib-font-size-12">TRF Bez: ' + job[9] + '</span>';
        }
        //check jobtype
        if (job[13] == "HAUFE") {
            this.jobTemplate = '<div class="' + this.alternateClass + ' animateJob row">' + '<div class="ib-jobs-description columns small-12 medium-8">' + '<span class="hide-for-small-only">' + job[6] + " | " + job[2] + '</span>' + '<a href="' + job[14] + '" target="_blank"">' + job[0] + '</a>' + trfTemplate + '<span class="hide-for-small-only ib-font-size-12">Chiffre: ' + job[3] + '</span>' + '</div>' + '<div class="ib-jobs-location columns small-12 medium-4">' + '<span>' + job[1] + '</span>' + '<span class="hide-for-small-only"><span class="RS_MESSAGE"><!-- Bundesland --></span>' + job[4] + '</span>' + '<span class="ib-font-size-12">Veröffentlicht am: ' + job[5] + '</span>' + '</div>' + '</div>';
        }
        else {
            this.jobTemplate = '<div class="' + this.alternateClass + ' animateJob row">' + '<div class="ib-jobs-description columns small-12 medium-8">' + '<span class="hide-for-small-only">' + job[6] + " | " + job[2] + '</span>' + '<a href="' + this.targetPage + "/" + job[7] + '" target="_blank"">' + job[0] + '</a>' + trfTemplate + '<span class="hide-for-small-only ib-font-size-12">Chiffre: ' + job[3] + '</span>' + '</div>' + '<div class="ib-jobs-location columns small-12 medium-4">' + '<span>' + job[1] + '</span>' + '<span class="hide-for-small-only"><span class="RS_MESSAGE"><!-- Bundesland --></span>' + job[4] + '</span>' + '<span class="ib-font-size-12">Veröffentlicht am: ' + job[5] + '</span>' + '</div>' + '</div>';
        }

       $(this.jobContainer!).append(this.jobTemplate);

        if (this.alternateClass == "odd") {
            this.alternateClass = "even";
        } else {
            this.alternateClass = "odd";
        }

        this.currentJob++;

    }

    initJobs() {
        $(this.jobContainer!).empty();
        this.currentJob = 0;




        this.currentResults = this.results.length;
        let limit = 1000;
        if (this.results.length < limit) {
            limit = this.results.length;
        }
        for (let i = 0; i < limit; i++) {
            this.addJobRow(this.results[i]);
        }
        this.updateResults();
    }

    resetSearch() {
        $('#ib-jobs-search').val('');
        $('select').prop('selectedIndex', 0);
        this.searchTerms('');
    }

    updateResults() {
        $(this.resultCounter!).html('' + this.results.length);
    }

    checkFixed() {
        let ibHeader = $('#ib-header');
        if (ibHeader.height() == 0) {
            ibHeader = $('.jetmenu');
        }

        let distance = $('#toStick').offset()!.top - ibHeader.offset()!.top;
        if (distance <= ibHeader.height()!) {
            $('.ib-jobs-search-container').addClass('ib-jobs-sticky');
            if (this.searchExtended) {
                $('#ib-jobs-lazy-container').addClass('ib-job-container-spacer-extended');
            } else {
                $('#ib-jobs-lazy-container').addClass('ib-job-container-spacer-default');
            }
        } else {
            $('.ib-jobs-search-container').removeClass('ib-jobs-sticky');
            $('#ib-jobs-lazy-container').removeClass('ib-job-container-spacer-extended');
            $('#ib-jobs-lazy-container').removeClass('ib-job-container-spacer-default');
        }

        if ($('.ib-jobs-search-container').hasClass('ib-jobs-sticky')) {
            $('.ib-jobs-search-container').css('top', ibHeader.height()!);
        }
    }

    setEvents() {

        //search extended
        $('.ib-jobs-extended-search-container').on('click', () => {
            this.searchExtended = !this.searchExtended;
            $('.ib-jobs-container #ib-jobs-extended-search').toggleClass('searchActive');
            $('.ib-jobs-container .ib-jobs-extended-search-container i').toggleClass('ib-icon-arrow-right ib-icon-arrow-down');

        });

        //set delay for inputkey
        var delay = this.makeDelay(350);

        //input key up
        $('#ib-jobs-search').on('keyup', () => {
            delay(() => {
                this.searchTerms($('#ib-jobs-search').val() as string);
            });

        });

        //select change
        $('.ib-jobs-ext-filter').on('change', () => {
            this.searchTerms(this.prepareExtFilterTerm(), true);
        });

        //reset button
        $('#ib-jobs-reset').on('click', () => {
            this.resetSearch();
        });

        window.addEventListener('resize', () => {
            this.setStickyWidth();
        });
    }

    prepareExtFilterTerm():Array<any> {
        let term:Array<any> = [];

        //var term = "";
        /*
        $.each($('.ib-jobs-ext-filter'),    (key, val) {
        if ($("option:selected", this).index() !== 0) {
        term = term + " " + $("option:selected", this).text();
        }
        });
        */

        document.querySelectorAll('.ib-jobs-ext-filter').forEach((element:Element) => {
            let value = (element as HTMLElement).dataset.val;
            const data:any = {};
            if ($("option:selected", element).index() !== 0) {
                data.val = value;
                data.text = $("option:selected", element).text();
                term.push(data);
            } else {
                data.val = value;
                data.text = 'true';
                term.push(data);
            }
        });

        return term;

    }

    setStickyWidth() {
        const tmpWidth:number = $('#toStick').width()!;
        $('.searchHeader').css('max-width', tmpWidth);
    }

    makeDelay(ms:number) {
        var timer = 0;
        return  (callback:any) => {
            window.clearTimeout(timer);
            timer = window.setTimeout(callback, ms);
        };
    };
}

export default IBJobSearch;
