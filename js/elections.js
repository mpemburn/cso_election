var CsoElection = {

};

jQuery(document).ready(function ($) {
    if (jQuery('.cso-election').is('*')) {
        electionNamespace.election = Object.create(CsoElection);
        electionNamespace.election.init();
    }
});