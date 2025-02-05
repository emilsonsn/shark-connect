import axios from "axios";

export default function consultCep(cep) {

    let cep2 = cep.replace(/\D/g, '');

    return axios.get(`https://viacep.com.br/ws/${cep2}/json/`);
}