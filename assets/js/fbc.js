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

    library: function(e) {
        this.setState({
            album: {
                name: 'Recent Images'
            },
            path: args.FBC_URL +"/includes/lib/get.php?subdomain="+ args.subdomain +"&token="+ args.token +"&limit=30&start=0"
		});
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

    render: function() {
        return (
            <div>
                <div id="fbc-search">
                    <Search onValueChange={this.handleSearch} />
                </div>
                <div id="fbc-tree" className="collapse">
                    <a href="javascript:;" onClick={this.library}>Main Library</a>

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
