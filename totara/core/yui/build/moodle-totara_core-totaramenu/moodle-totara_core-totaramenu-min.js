YUI.add("moodle-totara_core-totaramenu",function(e,t){M.coremenu=M.coremenu||{},NS=M.coremenu.setfocus=M.coremenu.setfocus||{},NS.init=function(){typeof $=="undefined"&&alert("jQuery is required for this to work"),$("#totaramenu, #custommenu").delegate("> ul > li > a","focus",function(){$(this).closest("ul").find("ul").removeAttr("style"),$(this).siblings("ul").show()})}},"@VERSION@",{requires:["jquery"]});
