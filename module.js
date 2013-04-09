M.block_custhome = {}

M.block_custhome.add_handles = function(Y) {
    M.block_custhome.Y = Y;
    YUI().use('dd-constrain', 'dd-proxy', 'dd-drop', 'dd-plugin','dd-delegate', function(Y) {
        //Static Vars
        var goingUp = false, lastY = 0;

        /*var list = Y.Node.all('#course_list .coursebox');
        list.each(function(v, k) {
            var dd = new Y.DD.Drag({
                node: v,
                target: {
                    padding: '0 0 0 20'
                }
            }).plug(Y.Plugin.DDProxy, {
                moveOnEnd: false
            }).plug(Y.Plugin.DDConstrained, {
                constrain2node: '#course_list'
            });
            dd.addHandle('.course_title .move');
        });
        */
        M.block_custhome.del = new Y.DD.Delegate({
            container: '#course_list', //The common container
            nodes: '.coursebox', //The items to make draggable
            target:true
        });

        var drops = Y.Node.all('#coursebox');
        drops.each(function(v, k) {
            var tar = new Y.DD.Drop({
                node: v
            });
        });

        M.block_custhome.del.on('drag:start', function(e) {
            
            
            //Get our drag object
            var drag = e.target;
            //Set some styles here
            drag.get('node').setStyle('opacity', '.25');
            drag.get('dragNode').addClass('block_custhome');
            drag.get('dragNode').set('innerHTML', drag.get('node').get('innerHTML'));
            drag.get('dragNode').setStyles({
                opacity: '.5',
                borderColor: drag.get('node').getStyle('borderColor'),
                backgroundColor: drag.get('node').getStyle('backgroundColor')
            });
        });

        M.block_custhome.del.on('drag:end', function(e) {
            var drag = e.target;
            //Put our styles back
            drag.get('node').setStyles({
                visibility: '',
                opacity: '1'
            });
            M.block_custhome.save(Y);
        });

        M.block_custhome.del.on('drag:drag', function(e) {
            //Get the last y point
            var y = e.target.lastXY[1];
            //is it greater than the lastY var?
            if (y < lastY) {
                //We are going up
                goingUp = true;
            } else {
                //We are going down.
                goingUp = false;
            }
            //Cache for next check
            lastY = y;
        });

        M.block_custhome.del.on('drop:over', function(e) {
            //Get a reference to our drag and drop nodes
            var drag = e.drag.get('node'),
            drop = e.drop.get('node');

            //Are we dropping on a li node?
            if (drop.hasClass('coursebox')) {
                //Are we not going up?
                if (!goingUp) {
                    drop = drop.get('nextSibling');
                }
                //Add the node to this list
                e.drop.get('node').get('parentNode').insertBefore(drag, drop);
                //Resize this nodes shim, so we can drop on it later.
                e.drop.sizeShim();
            }
        });

        M.block_custhome.del.on('drag:drophit', function(e) {
            var drop = e.drop.get('node'),
            drag = e.drag.get('node');

            //if we are not on an li, we must have been dropped on a ul
            if (!drop.hasClass('coursebox')) {
                if (!drop.contains(drag)) {
                    drop.appendChild(drag);
                }
            }
        });
        
        M.block_custhome.del.dd.plug(Y.Plugin.DDProxy, {
            moveOnEnd: false,
            cloneNode:true
        });



        M.block_custhome.del.dd.plug(Y.Plugin.DDConstrained, {
            constrain2node: '#course_list'
        });


        M.block_custhome.del.dd.plug(Y.Plugin.DDNodeScroll, {
            node: 'div'
        });

    });
}

M.block_custhome.save = function() {
    var Y = M.block_custhome.Y;
    var sortorder = Y.one('#course_list').get('children').getAttribute('id');
    for (var i = 0; i < sortorder.length; i++) {    
        if(isNaN(sortorder[i].substring(7))){
             if(Y.one('#'+sortorder[i] +' h2')){ //IF moving while in editing mode                 
                sortorder[i] = Y.one('#'+sortorder[i] +' h2').get("innerHTML"); 
             }else{
                sortorder[i] = Y.one('#'+sortorder[i] +' input').get("value");
             }
        }else{
            sortorder[i] = sortorder[i].substring(7);        
        }
    }
    var params = {
        sesskey : M.cfg.sesskey,
        sortorder : sortorder
    };
    Y.io(M.cfg.wwwroot+'/blocks/custhome/save.php', {
        method: 'POST',
        data: build_querystring(params),
        context: this
    });
}

/**
 * Init a collapsible region, see print_collapsible_region in weblib.php
 * @param {YUI} Y YUI3 instance with all libraries loaded
 * @param {String} id the HTML id for the div.
 * @param {String} userpref the user preference that records the state of this box. false if none.
 * @param {String} strtooltip
 */
M.block_custhome.collapsible = function(Y, id, userpref, strtooltip) {
    if (userpref) {
        M.block_custhome.userpref = true;
    }
    Y.use('anim', function(Y) {
        new M.block_custhome.CollapsibleRegion(Y, id, userpref, strtooltip);
    });
};

/**
 * Object to handle a collapsible region : instantiate and forget styled object
 *
 * @class
 * @constructor
 * @param {YUI} Y YUI3 instance with all libraries loaded
 * @param {String} id The HTML id for the div.
 * @param {String} userpref The user preference that records the state of this box. false if none.
 * @param {String} strtooltip
 */
M.block_custhome.CollapsibleRegion = function(Y, id, userpref, strtooltip) {
    // Record the pref name
    this.userpref = userpref;

    // Find the divs in the document.
    this.div = Y.one('#'+id);

    // Get the caption for the collapsible region
    var caption = this.div.one('#'+id + '_caption');
    caption.setAttribute('title', strtooltip);

    // Create a link
    var a = Y.Node.create('<a href="#"></a>');
    // Create a local scoped lamba function to move nodes to a new link
    var movenode = function(node){
        node.remove();
        a.append(node);
    };
    // Apply the lamba function on each of the captions child nodes
    caption.get('children').each(movenode, this);
    caption.prepend(a);

    // Get the height of the div at this point before we shrink it if required
    var height = this.div.get('offsetHeight');
    if (this.div.hasClass('collapsed')) {
        // Shrink the div as it is collapsed by default
        this.div.setStyle('height', caption.get('offsetHeight')+'px');
    }

    // Create the animation.
    var animation = new Y.Anim({
        node: this.div,
        duration: 0.3,
        easing: Y.Easing.easeBoth,
        to: {
            height:caption.get('offsetHeight')
            },
        from: {
            height:height
        }
    });

    // Handler for the animation finishing.
    animation.on('end', function() {
        this.div.toggleClass('collapsed');
    }, this);

    // Hook up the event handler.
    caption.on('click', function(e, animation) {
        e.preventDefault();
        // Animate to the appropriate size.
        if (animation.get('running')) {
            animation.stop();
        }
        animation.set('reverse', this.div.hasClass('collapsed'));
        // Update the user preference.
        if (this.userpref) {
            M.util.set_user_preference(this.userpref, !this.div.hasClass('collapsed'));
        }
        animation.run();
    }, this, animation);
};

M.block_custhome.del = null;
M.block_custhome.newcat = 0;
M.block_custhome.userpref = false;

/**
 * The user preference that stores the state of this box.
 * @property userpref
 * @type String
 */
M.block_custhome.CollapsibleRegion.prototype.userpref = null;

/**
 * The key divs that make up this
 * @property div
 * @type Y.Node
 */
M.block_custhome.CollapsibleRegion.prototype.div = null;

/**
 * The key divs that make up this
 * @property icon
 * @type Y.Node
 */
M.block_custhome.CollapsibleRegion.prototype.icon = null;

M.block_custhome.addCategory = function (e){
    var containerDiv = Y.one('#course_list');
    var newCategorydiv  =  Y.Node.create('<div/>').set("id","categorynew-"+ ++M.block_custhome.newcat).addClass('box coursebox');
    var categoryTitlediv = Y.Node.create('<div/>').set("id","category-titlenew-" + M.block_custhome.newcat).addClass("category_title").appendTo(newCategorydiv);
    
    var movediv = Y.Node.create('<div/>').addClass("move").appendTo(categoryTitlediv);
    var imgmove = Y.Node.create('<img/>').set('title',"Move").addClass("cursor").set('alt',"Move").set('src',M.cfg.wwwroot+"/theme/image.php?theme=elab_icorsi&amp;component=core&amp;image=i%2Fmove_2d").appendTo(movediv);
        
    var categoryTitle = Y.Node.create('<h2/>').addClass('title').setStyle("display","inline").set('innerHTML', 'Category Title').appendTo(categoryTitlediv);
        
    var editbuttons = Y.Node.create('<div/>').addClass("category_buttons").appendTo(categoryTitlediv);
        
    var imgedit = Y.Node.create('<img/>').set("id","edittitle__categorynew-" + M.block_custhome.newcat).addClass("edittitle_category").set('title',"Edit title").set('alt',"Edit title").set('src',M.cfg.wwwroot+"/theme/image.php?theme=elab_icorsi&amp;component=core&amp;image=t%2Feditstring").appendTo(editbuttons);
    var imgdelete = Y.Node.create('<img/>').set("id","delete__categorynew-" + M.block_custhome.newcat).addClass('delete_category').set('title',"Delete").set('alt',"Delete").set('src',M.cfg.wwwroot+"/theme/image.php?theme=elab_icorsi&amp;component=core&amp;image=t%2Fdelete").appendTo(editbuttons);
    var flushbuttonsdiv = Y.Node.create('<div/>').addClass('box flush').appendTo(editbuttons);   
    
    var flushcategorydiv =  Y.Node.create('<div/>').addClass('box flush').appendTo(newCategorydiv);   
    
    imgdelete.on('click', function(e) {            
        M.block_custhome.deleteCategory(e); 
    });
    imgedit.on('click', function(e) {            
        M.block_custhome.edittitleCategory(e); 
    });

    newCategorydiv.appendTo(containerDiv);

    M.block_custhome.del.syncTargets();
        
    M.block_custhome.save(); 
        
};

M.block_custhome.deleteCategory = function (e){   
    categorydiv = e.target.get("id").split("__")[1];
    
    Y.one('#'+categorydiv).remove();
    M.block_custhome.save(); 
};
M.block_custhome.edittitleCategory = function (e){   
    
    //Hide all edit images
    editimages = Y.all('.edittitle_category');
    editimages.each(function(v, k) {
          v.hide();
       });
        
    categorydiv = e.target.get("id").split("__")[1];
    element = Y.one('#'+categorydiv+' h2.title');
    element_old = element;
    
    elementdiv = Y.one('#'+categorydiv + ' div');
    
    
    var currenttitle = Y.one('#'+categorydiv+' h2.title');
    var oldtitle = currenttitle.get('innerHTML');

    // Handle events for edit_resource_title
    var listenevents = [];
    var thisevent;
    
    
    // Grab the anchor so that we can swap it with the edit form
    //var anchor = categorydiv.ancestor('a');
    var data = {
        'class'   : 'resource',
        'field'   : 'gettitle',
        'id'      : categorydiv
    };
    
    // Create the editor and submit button
    var editor = Y.Node.create('<input />')
        .setAttrs({
            'id'    : 'editcategorytitle',
            'name'  : 'title',
            'value' : element.get("innerHTML"),
            'autocomplete' : 'off'
        })
        .addClass('titleeditor');
    var editinstructions = Y.Node.create('<span />')
            .addClass('editcategorytitleinstructions')
            .set('innerHTML', "Escape to cancel, Enter when finished");
    var editform = Y.Node.create('<form />')
        .addClass('activityinstance')
        .set('id','formeditcategorytitle')
        .setAttribute('action', '#');
    
    // Clear the existing content and put the editor in
    currenttitle.set('data', '');
    editform.appendChild(editor);
    elementdiv.appendChild(editinstructions);
    element.replace(editform);
     
    e.preventDefault();

    // Focus and select the editor text
    editor.focus().select();
    // Handle removal of the editor
    var clear_edittitle = function() {
        // Detach all listen events to prevent duplicate triggers
        var thisevent;
        
        while (thisevent = listenevents.shift()) {            
            thisevent.detach();
        }        
        
        editinstructions.remove();
    }

    // Handle cancellation of the editor
    var cancel_edittitle = function(e) {
        clear_edittitle();    
        editform.replace(element_old);
        
        //Show all edit images
        editimages.each(function(v, k) {
          v.show();
       });
    };

    // Cancel the edit if we lose focus or the escape key is pressed
    thisevent = editor.on('blur', cancel_edittitle);
    listenevents.push(thisevent);
    
    thisevent = Y.one('document').on('keydown', function(e) {
        if (e.keyCode === 27) {
            e.preventDefault();
            cancel_edittitle(e);
        }
    });
    listenevents.push(thisevent);
    
    // Handle form submission
    thisevent = editform.on('submit', function(e) {
        // We don't actually want to submit anything
        e.preventDefault();
        
        // Clear the edit title boxes
        clear_edittitle();        
       
        var newtitle = Y.Lang.trim(editor.get('value')); 
        
        //Try to remove Tags here
        
        if (newtitle != null && newtitle != "" && newtitle != oldtitle) {
            editform.replace( Y.Node.create('<h2/>').addClass('title').setStyle("display","inline").set('innerHTML', newtitle));
        }else{
            editform.replace( Y.Node.create('<h2/>').addClass('title').setStyle("display","inline").set('innerHTML', oldtitle));
        }
       
        //Show all edit images
        editimages.each(function(v, k) {
          v.show();
        });
        //Save data
        M.block_custhome.save(); 
    });
        
    listenevents.push(thisevent);
   
};
M.block_custhome.addCategoryEvent = function (Y){
    var addCategory = Y.one('#addCategory');
    addCategory.on('click', function(e) {
        YUI().use('node', 'event','dd-constrain', 'dd-proxy', 'dd-drop', 'dd-plugin','dd-delegate', function(Y) {
            new M.block_custhome.addCategory(Y);
        });
    });
};

M.block_custhome.deleteCategoryEvent = function (Y){
    var delCategory = Y.all('.delete_category');
   
    delCategory.on('click', function(e) {        
        M.block_custhome.deleteCategory(e);
    });
};

M.block_custhome.edittitleCategoryEvent = function (Y){
    var edittitleCategory = Y.all('.edittitle_category');
   
    edittitleCategory.on('click', function(e) {       
        M.block_custhome.edittitleCategory(e);
    });
};

