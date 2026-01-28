# starting from node base image
FROM node:14-alpine

# chaging the working directory
WORKDIR /app

# for debugging purpose
# apk add --update --no-cache busybox-extras curl

# copying package & npm files and installing dependecies
COPY package.json .
COPY package-lock.json .
RUN npm install --production

# copying rest of the files
COPY . .

# starting the server
CMD [ "node", "index.js" ]