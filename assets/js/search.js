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

	handleSubmit: function(e) {
		e.preventDefault();
	},

    render: function() {
		var submit = this.props.onValueChange.bind(this,this.state.keyword);
        return (
			<form id="searchForm" onSubmit={this.handleSubmit}>
				<input onChange={this.onChange} value={this.state.keyword} placeholder="Global Search" />
				<i className="icon-search" onClick={submit}></i>
				<button onClick={submit}></button>
			</form>
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
