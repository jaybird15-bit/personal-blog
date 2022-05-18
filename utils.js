function newElement( html ) {
    let template = document.createElement( "template" );
    template.innerHTML = html.trim();
    return template.content.firstChild;
}