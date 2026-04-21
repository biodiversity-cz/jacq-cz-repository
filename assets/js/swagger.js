import SwaggerUIBundle from 'swagger-ui-dist/swagger-ui-bundle';
import 'swagger-ui-dist/swagger-ui.css';

export default function swagger() {

    const swaggerElement = document.getElementById("swagger-ui");
    if (!swaggerElement) {
        return;
    }

    SwaggerUIBundle({
        domNode: swaggerElement,
        url: '/api/public/v1/openapi/meta', //"https://petstore.swagger.io/v2/swagger.json" //
    });

}
