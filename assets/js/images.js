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

	componentDidMount: function() {

	},

	componentWillUpdate: function(nextProps,nextState) {

	},

	componentDidUpdate: function(prevProps,prevState) {
		React.render(<Attachment attachment={this.state.item} />, document.getElementById('fbc_media-sidebar') );
	},


    render: function() {

		if( this.state.start > 0 )
			var addMore = this.state.start;

        return (
			<span>
				{ this.props.data.map(function(item, i) {
					return (
						<li className="fbc_attachment attachment">
			                <div className="attachment-preview">
	                            <img src={item[0].img} onClick={this.handleClick.bind(this,item[0])} />
			                </div>
			            </li>
					);
				}, this)}
			</span>
        );
    }

});

var FlightImages = React.createClass({
	getInitialState: function() {
		return {
			src: this.props.path,
			album: {
                name: 'Recent Images'
            },
			start: parseInt(args.start),
			limit: parseInt(args.limit),
			data: []
		};
	},

	loadMore: function(e) {
		jQuery('#loader').show();
		this.setState({
			start: this.state.start+this.state.limit,
			src: args.FBC_URL +"/includes/lib/get.php?subdomain="+ args.subdomain +"&token="+ args.token +"&album="+ this.state.album.id +"&limit="+ this.state.limit +"&start="+ this.state.start
		});
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
			ext[0] = ext[0].toLowerCase();

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
				jQuery('#loader').hide();
        })
		.always(function() {
			//console.log('here');
		});
    },

	componentDidMount: function() {
		jQuery('#loader').show();
		var self = this;
		$.ajax({
			url: this.state.src,
			dataType: 'json',
			cache: false
		})
		.done(function(data) {
			var cnt = 1;
            $.each(data, function(k,v) {
                self.repeat(v,cnt);
				cnt++;
            });
		});
	},

	componentWillUpdate: function(nextProps,nextState) {
		if(nextProps.album.id != this.props.album.id) {
			this.setState({
				album: nextProps.album,
				data: [],
				src: args.FBC_URL +"/includes/lib/get.php?subdomain="+ args.subdomain +"&album="+ nextProps.album.id +"&token="+ args.token
			});
		}
	},

	looper: function() {
		var self = this;
		$.ajax({
			url: this.state.src,
			dataType: 'json',
			cache: false
		})
		.done(function(data) {
			var cnt = 1;
			$.each(data, function(k,v) {
				self.repeat(v,cnt);
				cnt++;
			});
		});
	},

	componentDidUpdate: function(prevProps,prevState) {
		if(prevProps.album.id != this.props.album.id) {
			jQuery('#loader').show();
			this.looper();
		}

		if(this.state.start > prevState.start) {
			this.looper();
		}
	},

    render: function() {
        return (
			<div class="grid">
				<h1 className="text-center">{this.state.album.name}</h1>
				<ul className="attachments" id="__attachments-view-fbc">

	                <Images data={this.state.data} />

					<div id="fbc_loadMore_wrap">
						<button className="btn" id="fbc_loadMore" onClick={this.loadMore}>Load More</button>
					</div>
	            </ul>
			</div>
        );
    }
});

//React.render(<FlightImages />, document.getElementById('fbc-loop') );
