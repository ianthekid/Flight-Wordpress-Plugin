var Children = React.createClass({
	render: function() {
		return (
			<div>
			{ this.props.item.map(function(item) {
				var children = [];
				if( item.children ) {
					for (var i = 0; i < item.children.length; i++) {

						if( item.children[i].scheme == "album" ) {
							icon = "fa fa-database";
							var click = this.props.onClick.bind(this,item.children[i]);
						} else {
							icon = "fa fa-folder-open";
							var click = '';
						}

						children.push(<li><i className={icon} onClick={click}></i><a href="javascript:;" onClick={click}>{item.children[i].name}</a></li>);
					}
					var showChildren = <Children item={item.children} />;
				}

				if( item.scheme == "album" ) {
					icon = "fa fa-database";
					var click = this.props.onClick.bind(this,item);
				} else {
					icon = "fa fa-folder-open";
					var click = '';
				}

				return (
					<ul>
						<li>
							<i className={icon} onClick={click}></i>
							<a href="javascript:;" onClick={click}>{item.name}</a>


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

	handleChange: function(e) {
		this.props.onValueChange(e);
	},

    render: function() {
        return (
			<span>
				{ this.props.data.map(function(item, i) {

					if( item.children ) {
						var c = "parent_li";
						var f = "fa fa-folder";
						var click = this.handleClick.bind(this,item);
						var showChildren = <Children item={item.children} onClick={this.handleChange} />;
					} else {
						var c = "";
						var f = "fa fa-database";
						var click = this.props.onValueChange.bind(this,item);
					}

					var id = "parent_"+item.id;

					return (
						<li className={c} id={id}>
							<i className={f} onClick={click}></i>
							<a href="javascript:;" onClick={click}>{item.name}</a>

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

	handleChange: function(e) {
		this.props.onValueChange(e);
	},

    render: function() {
        return (
			<div className="tree well">
				<ul>
	                <Folders data={this.state.data} onValueChange={this.handleChange} />
	            </ul>
			</div>
        );
    }
});
