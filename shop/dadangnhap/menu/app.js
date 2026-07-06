class Menu {
    constructor(menu_list = []) {
        this.menu = menu_list;
        this.menu_obj = [];
        this.menu_html = "";
    }
    renderMenuObject(){
        var deep = 0;
        this.getMainParents().forEach(p => {
            this.menu_obj.push( this.renderMenuItem(p,deep) );
        })
    }
    renderMenuItem(menu_item,deep) {
        let o = {
            deep: deep,
            title: menu_item.title,
            url: menu_item.url,
            childs: []
        }
        deep += 1;
        this.getSubs( this.menu.indexOf(menu_item) ).forEach(sub => {    
            o.childs.push( this.renderMenuItem(sub,deep) )
        });
        return o;

    }
    getSubs(index) {
        let deep_parent = this.countUnder( this.menu[index].title );
        let subs = [];
        for (let i = index+1; i < this.menu.length; i++) {
            const menu_item = this.menu[i];

            if( this.countUnder(menu_item.title) == deep_parent+1 ){
                subs.push(menu_item);
            }else if( this.countUnder(menu_item.title) > deep_parent+1 ){
                continue;
            }else{
                break;
            }
        }
        return subs;
    }
    countUnder(s) {
        let arr = Array.from(s);
        let x = "_";
        let count = 0;
        for (let i = 0; i < arr.length; i++) {
            const item = arr[i];
            if (item == "_" && x == "_") {
                count += 1;
                x = item;
            }
        }
        return count;
    }
    getMainParents(){
        let parents = [];
        for (let i = 0; i < this.menu.length; i++) {
            const menu_item = this.menu[i];

            if( this.countUnder(menu_item.title) == 0 ){
                parents.push(menu_item);
            }
        }
        return parents;
    }

    clearUnder(s){
        let arr = Array.from(s);
        let title = ""
        for (let i = 0; i < arr.length; i++) {
            const item = arr[i];
            if (item != "_") {
                title += item;
            }
        }
        return title;
    }

    renderMenuItemHtml(menu_item){
        var $el = $("<li>",{class:"sd-menu-item"});
     
        if(menu_item.childs.length > 0){
            $el.append(
                $("<span>").html(`<a href="${menu_item.url}">${ this.clearUnder( menu_item.title )}</a> <i class="material-icons">keyboard_arrow_right</i>`)
            );
            $el.addClass("sd-has-sub-menu");
            
            let $sub_list = $("<ul>",{class:`sd-sub-menu${menu_item.deep+1}-list`});
            menu_item.childs.forEach(sub => {
                
                $sub_list.html( $sub_list.html() + this.renderMenuItemHtml(sub) );
            });
    
            $el.html( $el.html() + $sub_list[0].outerHTML);
        }else{
            $el.append(
                $("<a>",{href: menu_item.url}).html(`${ this.clearUnder( menu_item.title )}`)
            )
        }
        
        return $el[0].outerHTML;

    }

    renderMenuHTML(){
        this.renderMenuObject();
        var $el = $("<ul>",{class:"sd-menu-list"});
        this.menu_obj.forEach(p => {
            this.menu_html += this.renderMenuItemHtml(p)
        })
        $el.html(this.menu_html);

        this.menu_html = $el[0].outerHTML;
    }
}


jQuery(document).ready(function(){
    let $ = jQuery;

    $.fn.addMenu = function(menu_list){
        var menu = new Menu(menu_list);
        menu.renderMenuHTML();

        $(this).html(menu.menu_html);
    }

    $.fn.mobileMenuInit = function(){
        $(".sd-menu-list .sd-has-sub-menu i").click(function(e){
            e.preventDefault();
            e.stopPropagation();
            let $icon = $(e.currentTarget);
            $icon.text() != "keyboard_arrow_down" ? $icon.text("keyboard_arrow_down") : $icon.text("keyboard_arrow_right");
            $(e.currentTarget).closest("li").children("ul").slideToggle();
        })

        $(".sd-menu-toggle").click(function(e){
            e.preventDefault();
            e.stopPropagation();
            $(".sd-mobile-menu").slideToggle();
        });
    }
})

