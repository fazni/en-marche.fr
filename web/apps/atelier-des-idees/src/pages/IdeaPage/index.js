import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import IdeaPageBase from '../IdeaPageBase';
import { DELETE_IDEA_MODAL, PUBLISH_IDEA_MODAL } from '../../constants/modalTypes';
import { showModal } from '../../redux/actions/modal';
import { initIdeaPage } from '../../redux/thunk/navigation';
import {
    saveCurrentIdea,
    publishCurrentIdea,
    deleteCurrentIdea,
    goBackFromCurrentIdea,
} from '../../redux/thunk/currentIdea';
import { selectAuthUser } from '../../redux/selectors/auth';
import { selectCurrentIdea, selectGuidelines } from '../../redux/selectors/currentIdea';

class IdeaPage extends React.Component {
    componentDidMount() {
        this.props.initIdeaPage();
    }

    render() {
        return this.props.guidelines.length ? <IdeaPageBase {...this.props} /> : null;
    }
}

IdeaPage.propTypes = {
    initIdeaPage: PropTypes.func.isRequired,
};

function mapStateToProps(state) {
    // TODO: handle loading and error
    const currentUser = selectAuthUser(state);
    // guidelines
    const guidelines = selectGuidelines(state);
    // get and format current idea
    const idea = selectCurrentIdea(state);
    const { author, published_at, ...ideaData } = idea;
    const formattedIdea = {
        ...ideaData,
        authorName: author ? `${author.first_name} ${author.last_name}` : '',
        publishedAt: published_at && new Date(published_at).toLocaleDateString(),
    };
    return {
        idea: formattedIdea,
        guidelines,
        isAuthor: !!author && author.uuid === currentUser.uuid,
    };
}

function mapDispatchToProps(dispatch, ownProps) {
    // TODO: replace with actual action creators
    return {
        initIdeaPage: () => {
            const { id } = ownProps.match.params;
            dispatch(initIdeaPage(id));
        },
        onBackClicked: () => dispatch(goBackFromCurrentIdea()),
        onPublishIdea: (data) => {
            const { id } = ownProps.match.params;
            dispatch(
                showModal(PUBLISH_IDEA_MODAL, {
                    id,
                    submitForm: ideaData => dispatch(publishCurrentIdea({ ...ideaData, ...data })),
                })
            );
        },
        onDeleteClicked: () =>
            dispatch(
                showModal(DELETE_IDEA_MODAL, {
                    onConfirmDelete: () => dispatch(deleteCurrentIdea()),
                })
            ),
        onSaveIdea: data => dispatch(saveCurrentIdea(data)),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(IdeaPage);
