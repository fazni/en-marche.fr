import React from 'react';
import PropTypes from 'prop-types';

class Banner extends React.PureComponent {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <div className="banner">
                <div className="banner__container__close">
                    <button
                        className="banner__container__close button--transparent"
                        onClick={() => this.props.onClose()}
                    >
                        <img src="/assets/img/icn_close-white.svg" />
                    </button>
                </div>
                <div className="banner__container__titles">
                    <h3 className="banner__container__titles__title">{this.props.title}</h3>
                    <h4 className="banner__container__titles__subtitle">{this.props.subtitle}</h4>
                </div>
                <div className="banner__container">
                    <a href={this.props.link} className="banner__container__link button--secondary" target="_blank">
                        {this.props.linkLabel}
                    </a>
                </div>
            </div>
        );
    }
}

Banner.propTypes = {
    title: PropTypes.string.isRequired,
    subtitle: PropTypes.string.isRequired,
    link: PropTypes.string.isRequired,
    linkLabel: PropTypes.string.isRequired,
    onClose: PropTypes.func.isRequired,
};

export default Banner;
