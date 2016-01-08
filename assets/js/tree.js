var Children = React.createClass({

	handleClick: function(item,e) {
		console.log(item.id);
	},

	render: function() {
		//console.log(this.props.item);
		return (
			<div>
			{ this.props.item.map(function(item) {
				var children = [];
				if( item.children ) {
					for (var i = 0; i < item.children.length; i++) {

						if( item.children[i].scheme == "album" )
							icon = "fa fa-database";
						else
							icon = "fa fa-folder-open";

						children.push(<li><i className={icon}></i><a href="javascript:;" onClick={this.handleClick.bind(this,item.children[i])}>{item.children[i].name}</a></li>);
					}
					var showChildren = <Children item={item.children} />;
				}

				if( item.scheme == "album" )
					icon = "fa fa-database";
				else
					icon = "fa fa-folder-open";


				return (
					<ul>
						<li>
							<i className={icon}></i><a href="javascript:;" onClick={this.handleClick.bind(this,item)}>{item.name}</a>

							<ul>
								{children}
							</ul>
						</li>
					</ul>
				);
			}, this)}
			</div>
		);
	}

});

var Folders = React.createClass({

	handleClick: function(item,e) {
		this.setState({
			item: [item]
		});
	},

	componentDidMount: function() {
		jQuery(React.findDOMNode(this.refs.tooltip)).hide();

	},

	componentWillUpdate: function(nextProps,nextState) {

	},

	componentDidUpdate: function(prevProps,prevState) {

	},

	handleClick: function(item,e) {
		var children = jQuery("#parent_"+ item.id +">div");

		if (children.is(":visible")) {
			jQuery("#parent_"+ item.id +">i").removeClass("fa-folder-open");
			jQuery("#parent_"+ item.id +">i").addClass("fa-folder");
			children.hide('fast');
		} else {
			jQuery("#parent_"+ item.id +">i").removeClass("fa-folder");
			jQuery("#parent_"+ item.id +">i").addClass("fa-folder-open");
			children.show('fast');
		}
		//e.stopPropagation();
	},

    render: function() {
        return (
			<span>
				{ this.props.data.map(function(item, i) {

					if( item.children ) {
						var c = "parent_li";
						var f = "fa fa-folder";
						var showChildren = <Children item={item.children} />;
					} else {
						var c = "";
						var f = "fa fa-database";
					}

					var id = "parent_"+item.id;

					// onClick={this.handleClick.bind(this,item)}
					return (
						<li className={c} id={id}>
							<i className={f} onClick={this.handleClick.bind(this,item)}></i><a href="">{item.name}</a>

							{showChildren}
			            </li>
					);
				}, this)}
			</span>
        );
    }

});

var Tree = React.createClass({
	getInitialState: function() {
		return {
			src: args.FBC_URL +"/includes/lib/tree.php?subdomain="+ args.subdomain +"&token="+ args.token,
			data: []
		};
	},

	componentDidMount: function() {
		var self = this;
		$.ajax({
			url: this.state.src,
			dataType: 'json',
			cache: false
		})
		.done(function(data) {
			self.setState({data: data});
		});
	},

	componentWillUpdate: function(nextProps,nextState) {
	},

	componentDidUpdate: function(prevProps,prevState) {
	},

    render: function() {
        return (
			<div className="tree well">
				<ul>
	                <Folders data={this.state.data} />
	            </ul>
			</div>
        );
    }
});

React.render(<Tree />, document.getElementById('fbc-tree') );
