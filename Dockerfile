# RENDER BLUEPRINT - Este Dockerfile es un placeholder
# Render buscará render.yaml automáticamente para el deployment real
# Los servicios reales están definidos en render.yaml

FROM alpine:latest

WORKDIR /app

RUN echo "Este es un placeholder. Render debería usar render.yaml para el deployment. Si ves este mensaje, revisa la configuración del Blueprint en Render UI."

CMD ["echo", "Blueprint placeholder - ver render.yaml"]
