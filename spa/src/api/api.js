function fetchCollection(path) {
    return fetch(ENV_API_ENDPOINT + path).then(resp => resp.json()).then(json => json['member']);
}

export function findConferences() {
    return fetchCollection('api/conferences');
}

export function findComments(conference) {
    return fetchCollection('api/comments?conference=' + conference.id)
        .then(comments => {
            const commentPromises = comments.map(comment =>
                // Elimina la barra al final de ENV_API_ENDPOINT si existe antes de concatenar
                fetch(ENV_API_ENDPOINT.replace(/\/$/, '') + comment['@id'])  // Elimina la barra final si existe
                    .then(response => response.json())
            );

            return Promise.all(commentPromises);
        });
}