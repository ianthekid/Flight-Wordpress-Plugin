var Images = React.createClass({

	getInitialState: function() {
		return {
			start: parseInt(args.start),
			limit: parseInt(args.limit),
			item: []
		};
	},

	handleClick: function(item,e) {
		this.setState({
			item: [item]
		});
	},

	loadMore: function(e) {
		this.setState({
			start: this.state.start+this.state.limit
		});
	},

	componentDidMount: function() {

	},

	componentWillUpdate: function(nextProps,nextState) {

	},

	componentDidUpdate: function(prevProps,prevState) {
		React.render(<Attachment attachment={this.state.item} />, document.getElementById('fbc_media-sidebar') );

		if( this.state.start > prevState.start ) {
			var path = args.FBC_URL +"/includes/lib/get.php?subdomain="+ args.subdomain +"&token="+ args.token +"&limit="+ this.state.limit +"&start="+ this.state.start;
			console.log(this.state.start);
			//React.render(<FlightImages source={path} />, document.getElementById('loadMoreWrap') );
		}
	},


    render: function() {

		if( this.state.start > 0 )
			var addMore = this.state.start;

        return (
            <ul className="attachments" id="__attachments-view-fbc">
				{ this.props.data.map(function(item, i) {
					return (
						<li className="fbc_attachment attachment">
			                <div className="attachment-preview">
	                            <img src={item[0].img} onClick={this.handleClick.bind(this,item[0])} />
			                </div>
			            </li>
					);
				}, this)}
				<li className="fbc_attachment attachment">
					<div className="attachment-preview">
						{addMore}
					</div>
				</li>
				<div id="loadMoreWrap">
				</div>
				<div id="fbc_loadMore_wrap">
					<button className="btn" id="fbc_loadMore" onClick={this.loadMore}>Load More</button>
				</div>
            </ul>
        );
    }

});

var FlightImages = React.createClass({
    getInitialState: function() {
        return {
			data: []
        };
    },

    repeat: function(item,cnt) {
        var self = this;
        $.ajax({
            url: args.FBC_URL +"/includes/lib/download.php?id="+ item.id +"&subdomain="+ args.subdomain +"&token="+ args.token
		})
		.done(function(e) {
			var start = e.search("Location: ");
			var stop = e.search("Server: ");
			var img = e.substring( (start+10) ,stop);

			var fileExt = img.split('.').pop();
			var ext = fileExt.split('%');

			if(ext[0] == "jpg" || ext[0] == "jpeg" || ext[0] == "gif" || ext[0] == "png" || ext[0] == "pdf") {
                var image = [{
                    "id": item.id,
                    "name": item.name,
                    "owner": item.owner,
                    "ownerName": item.ownerName,
                    "size": item.size,
                    "time": item.time,
                    "img": img
                }];

				var arr = self.state.data.slice();
                arr.push(image);
                self.setState({data: arr});
			}

			if(cnt == args.limit)
				jQuery("#fbc_loadMore_wrap").show();
        })
		.always(function() {
			//console.log('here');
		});
    },

	componentDidUpdate: function() {
	},

	componentDidMount: function() {
		var self = this;
		$.ajax({
			url: this.props.source,
			dataType: 'json',
			cache: false
		})
		.done(function(data) {
			console.log(data.length);
			var cnt = 1;
            $.each(data, function(k,v) {
                self.repeat(v,cnt);
				cnt++;
            });
		})
		.always(function() {

		});
	},

    render: function() {
        return (
            <div class="grid">
                <Images data={this.state.data} />
            </div>
        );
    }
});

var path = args.FBC_URL +"/includes/lib/get.php?subdomain="+ args.subdomain +"&token="+ args.token +"&limit="+ args.limit +"&start="+ args.start;
React.render(<FlightImages source={path} />, document.getElementById('fbc-loop') );
