var Attachment = React.createClass({

    render: function() {
        var divStyle = {
          color: 'red'
        };

        var displayItem = (item) => <div style={divStyle}>{item}</div>;

        return (
            <div>
                { this.props.src.map(displayItem) }
            </div>
        );
    }

});
