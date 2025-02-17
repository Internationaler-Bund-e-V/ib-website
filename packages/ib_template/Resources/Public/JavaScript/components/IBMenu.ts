/* */
import $ from 'jquery';

type IBMenuSettings = {
    indicator?: boolean,
    speed?: number,
    delay?: number,
    hideDelay?: number,
    hideClickOut?: boolean,
    align?: string,
    submenuTrigger?: string,
    scrollable?: boolean,
    scrollableMaxHeight?: number
}

class IBMenu {
    protected settings:IBMenuSettings = {
        indicator: true,
        speed: 300,
        delay: 0,
        hideDelay: 0,
        hideClickOut: true,
        align: "left",
        submenuTrigger: "click",
        scrollable: true,
        scrollableMaxHeight: 400
    }
    protected menu:JQuery<HTMLElement>;
    protected menuWrapper:JQuery<HTMLElement> | undefined;
    protected lastScreenWidth:number = 0;
    protected bigScreen:boolean = false;

    constructor(menuElement:HTMLElement, options: IBMenuSettings|null = null) {
        $.extend(this.settings, options);
        this.menu = $(menuElement);
        if (!this.menu) {
            return;
        }

        this.menu.wrap('<div class="jetmenu-wrapper"></div>');
        this.menuWrapper = this.menu.parent();
        if (!this.menuWrapper) {
            return;
        }

        this.lastScreenWidth = this.windowWidth();

        if (this.settings.indicator == true) {
            this.menu.find("a").each((index, element:HTMLAnchorElement) => {
                if ($(element).siblings(".dropdown, .megamenu").length > 0) {
                    $(element).append("<span class='indicator'>+</span>");
                }
            });
        }

        // apply class 'submenu--open' when a megamenu appears
        $('.megamenu').on('appear', function(event, $all_appeared_elements) {
            $(this).closest('li').addClass('submenu--open');
        });
        // remove class 'submenu--open' when a megamenu appears
        $('.megamenu').on('disappear', function(event, $all_disappeared_elements) {
            $(this).closest('li').removeClass('submenu--open');
        });


        this.screenSize();

        window.addEventListener('resize', () => {
            if (this.lastScreenWidth <= 767 && this.windowWidth() > 767) {
                this.unbindEvents();
                this.hideCollapse();
                this.bindHover();
                $('.showhidemobile').removeClass('mobilenav--open');
                if (this.settings.align == "right" && this.bigScreen == false) {
                    this.rightAlignMenu();
                    this.bigScreen = true;
                }
            }
            if (this.lastScreenWidth > 767 && this.windowWidth() <= 767) {
                $('.jetmenu').show();
                $('.mobileLoader').hide();
                this.unbindEvents();
                this.showCollapse();
                this.bindClick();
                if (this.bigScreen == true) {
                    this.rightAlignMenu();
                    this.bigScreen = false;
                }
            }
            if (this.settings.align == "right") {
                if (this.lastScreenWidth > 767 && this.windowWidth() > 767)
                    this.fixSubmenuRight();
            }
            else {
                if (this.lastScreenWidth > 767 && this.windowWidth() > 767)
                    this.fixSubmenuLeft();
            }
            this.lastScreenWidth = this.windowWidth();
        });
    }


    screenSize() {
        if (this.windowWidth() <= 767) {
            this.showCollapse();
            this.bindClick();
            if (this.bigScreen == true) {
                this.rightAlignMenu();
                this.bigScreen = false;
            }
        }
        else {
            this.hideCollapse();
            this.bindHover();
            if (this.settings.align == "right") {
                this.rightAlignMenu();
                this.bigScreen = true;
            }
            else {
                this.fixSubmenuLeft();
            }
        }
        $('.jetmenu').show();
        $('.mobileLoader').hide();
    }

    bindHover() {
        if (navigator.userAgent.match(/Mobi/i) || window.navigator.maxTouchPoints > 0 || this.settings.submenuTrigger == "click") {
            this.menu.find("a").on("click touchstart", (e) => {
                e.stopPropagation();
                e.preventDefault();
                $(e.currentTarget).parent("li").siblings("li").find(".dropdown, .megamenu").stop(true, true).fadeOut(this.settings.speed);
                if ($(e.currentTarget).siblings(".dropdown, .megamenu").css("display") == "none") {
                    $(e.currentTarget).siblings(".dropdown, .megamenu").stop(true, true).delay(this.settings.delay!).fadeIn(this.settings.speed);
                    return false;
                }
                else {
                    $(e.currentTarget).siblings(".dropdown, .megamenu").stop(true, true).fadeOut(this.settings.speed);
                    $(e.currentTarget).siblings(".dropdown").find(".dropdown").stop(true, true).fadeOut(this.settings.speed);
                    if ($(e.currentTarget).siblings(".dropdown, .megamenu").length) {
                        return false;
                    }
                }
                if ($(e.currentTarget).attr("target") == "_blank" || $(e.currentTarget).attr("target") == "blank") {
                    window.open($(e.currentTarget).attr("href"));
                }
                else {
                    window.location.href = $(e.currentTarget).attr("href")!;
                }
            });

            this.menu.find("li").on("mouseleave", (event) => {
                $(event.target).children(".dropdown, .megamenu").stop(true, true).fadeOut(this.settings.speed);
            });

            if (this.settings.hideClickOut == true) {
                $(document).on("click.menu touchstart.menu", (event) => {
                    if ($(event.target).closest(this.menu).length == 0) {
                        this.menu.find(".dropdown, .megamenu").fadeOut(this.settings.speed);
                    }
                });
            }
        }
        else {
            this.menu.find("li").on("mouseenter", (e) => {
                $(e.currentTarget).children(".dropdown, .megamenu").stop(true, true).delay(this.settings.delay!).fadeIn(this.settings.speed);
            }).on("mouseleave", (e) => {
                $(e.currentTarget).children(".dropdown, .megamenu").stop(true, true).delay(this.settings.hideDelay!).fadeOut(this.settings.speed);
            });
        }
    }

    bindClick() {
        this.menu.find("li:not(.showhide)").each((index:number, element:HTMLElement)  => {
            if ($(element).children(".dropdown, .megamenu").length > 0) {
                $(element).children("a").on("click", (event) => {
                    if ($(event.currentTarget).siblings(".dropdown, .megamenu").css("display") == "none") {
                        $(event.currentTarget).siblings(".dropdown, .megamenu").delay(this.settings.delay!).slideDown(this.settings.speed).trigger('focus');
                        $(event.currentTarget).parent("li").siblings("li").find(".dropdown, .megamenu").slideUp(this.settings.speed);
                        return false;
                    }
                    else {
                        $(event.currentTarget).siblings(".dropdown, .megamenu").slideUp(this.settings.speed).trigger('focus');
                        //firstItemClick = 1;
                        return false;
                    }
                });
            }
        });
    }

    showCollapse() {
        this.menu.children("li:not(.showhide)").hide(0);
        this.menu.children("li.showhide").show(0);
        this.menu.find(".showhidemobile").on("click", (event) => {
            $(event.currentTarget).toggleClass('mobilenav--open');
            if (this.menu.children("li").is(":hidden")) {
                this.menu.children("li").slideDown(this.settings.speed);
                this.scrollable(true);
            }
            else {
                this.menu.children("li:not(.showhide)").slideUp(this.settings.speed);
                this.menu.children("li.showhide").show(0);
                this.menu.find(".dropdown, .megamenu").hide(this.settings.speed);
                this.scrollable(false);
            }
        });
    }

    hideCollapse() {
        this.menu.children("li").show(0);
        this.menu.children("li.showhide").hide(0);
        this.scrollable(false);
    }

    rightAlignMenu() {
        this.menu.children("li").addClass("jsright");
        var items = this.menu.children("li");
        this.menu.children("li:not(.showhide)").detach();
        for (var i = items.length; i >= 1; i--) {
            this.menu.append(items[i]);
        }
        this.fixSubmenuRight();
    }

    fixSubmenuRight() {
        this.menu.children("li").removeClass("last");
        let items = this.menu.children("li");
        for (let i = 1; i <= items.length; i++) {
            if ($(items[i]).children(".dropdown, .megamenu").length === 0) {
                continue;
            }

            let lastItemsWidth:number = 0;
            for (var y = 1; y <= i; y++) {
                lastItemsWidth += $(items[y]).outerWidth()!;
            }

            if ($(items[i]).children(".dropdown, .megamenu").outerWidth()! > lastItemsWidth) {
                $(items[i]).addClass("last");
            }
        }
    }

    fixSubmenuLeft() {
        this.menu.children("li").removeClass("fix-sub");
        let items = this.menu.children("li");
        if (items.length === 0) {
            return;
        }
        const menuWidth = this.menu.outerWidth()!;
        var itemsWidth = 0;
        for (var i = 1; i <= items.length; i++) {
            if ($(items[i]).children(".dropdown, .megamenu").length > 0) {
                if ($(items[i]).position().left + $(items[i]).children(".dropdown, .megamenu").outerWidth()! > menuWidth) {
                    $(items[i]).addClass("fix-sub");
                }
            }
        }
    }

    unbindEvents() {

        this.menu.find("li, a").off();
        this.menu.find(".showhidemobile").off();
        $(document).off("click.menu touchstart.menu");
        this.menu.find(".dropdown, .megamenu").hide(0);
    }

    windowWidth() {
        return window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
    }

    scrollable(flag:boolean) {
        if (this.settings.scrollable) {
            if (flag) {
                this.menuWrapper!.css("max-height", this.settings.scrollableMaxHeight!).addClass("scrollable");
            }
            else {
                this.menuWrapper!.css("max-height", "auto").removeClass("scrollable");
            }
        }
    }
}

export default IBMenu;
