var FBC = React.createClass({
    getInitialState: function() {
		return {
			album: [{
                name: 'Recent Images'
            }],
            search: '',
            path: args.FBC_URL +"/includes/lib/get.php?subdomain="+ args.subdomain +"&token="+ args.token +"&limit=30&start=0"
		};
	},

    handleChange: function(e) {
        this.setState({
			album: e,
            path: args.FBC_URL +"/includes/lib/get.php?subdomain="+ args.subdomain +"&album="+ e.id +"&token="+ args.token
		});
    },

    handleSearch: function(e) {
        this.setState({
            search: e
		});
    },

    toggle: function(e) {
        var tree = jQuery('#fbc-tree');
	    if (tree.is(':visible')){
	        tree.animate({"left":"-250px"}, "fast").hide();
			jQuery('#hideShow>i').addClass('fa-bars');
			jQuery('#hideShow>i').removeClass('fa-close');
			jQuery('#fbc-loop').css({'margin-left':'0px' });
	    } else {
	        tree.animate({"left":"0px"}, "fast").show();
			jQuery('#hideShow>i').removeClass('fa-bars');
			jQuery('#hideShow>i').addClass('fa-close');
			jQuery('#fbc-loop').css({'margin-left':'250px' });
	    }
    },

    render: function() {
        return (
            <div>
                <div className="searchRow">
                    <a className="btn" id="hideShow" onClick={this.toggle}> <i className="icon-library"></i> Library</a>
                    <Search onValueChange={this.handleSearch} />
                </div>

                <div id="fbc-tree" className="collapse">
                    <Tree onValueChange={this.handleChange} />
                </div>
                <div id="fbc-loop">
                    <FlightImages search={this.state.search} album={this.state.album} path={this.state.path} />
                </div>
            </div>
        );
    }
});

React.render(<FBC />, document.getElementById('fbc-react') );
