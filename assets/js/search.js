var Keyword = React.createClass({
	getInitialState: function() {
        return {
            keyword: null
        };
    },

	onChange: function(e) {
        var keyword = e.target.value;
        this.setState({ keyword: keyword });
    },

    render: function() {
		var submit = this.props.onValueChange.bind(this,this.state.keyword);
        return (
			<div>
				<input onChange={this.onChange} value={this.state.keyword} placeholder="Global Search" />
				<button onClick={submit}>Go</button>
			</div>
        );
    }
});

var Search = React.createClass({
	handleChange: function(e) {
		this.props.onValueChange(e);
	},

    render: function() {
        return (
			<div>
                <Keyword onValueChange={this.handleChange} />
			</div>
        );
    }
});
