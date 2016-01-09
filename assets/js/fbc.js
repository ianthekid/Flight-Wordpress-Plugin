var FBC = React.createClass({
    getInitialState: function() {
		return {
			album: [{
                name: 'Recent Images'
            }],
            path: args.FBC_URL +"/includes/lib/get.php?subdomain="+ args.subdomain +"&token="+ args.token +"&limit=30&start=0"
		};
	},

    handleChange: function(e) {
        this.setState({
			album: e,
            path: args.FBC_URL +"/includes/lib/get.php?subdomain="+ args.subdomain +"&album="+ e.id +"&token="+ args.token
		});
    },

    componentWillUpdate: function(nextProps,nextState,e) {
        //console.log(nextState.album.name);
        //console.log(this.state);
    },

    render: function() {
        return (
            <div>
                <div id="fbc-tree">
                    <Tree onValueChange={this.handleChange} />
                </div>
                <div id="fbc-loop">
                    <FlightImages album={this.state.album} path={this.state.path} />
                </div>
            </div>
        );
    }
});

React.render(<FBC />, document.getElementById('fbc-react') );
